<x-marketing-layout>
    <x-slot name="title">White-Label Ticketing Platform - Launch Your Own for Free</x-slot>
    <x-slot name="description">Launch a white-label ticketing platform for free. Open source, multi-tenant, Stripe billing built in. Set your own prices, keep 100% of revenue. See the demo.</x-slot>
    <x-slot name="breadcrumbTitle">White-Label SaaS</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "@id": "{{ url()->current() }}#software",
        "name": "Event Schedule White-Label Ticketing Platform",
        "url": "{{ url()->current() }}",
        "description": "Free, open source white-label ticketing platform with multi-tenant subscription billing built in. Selfhost it, set your own prices, and keep 100% of revenue.",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Ticketing Software",
        "operatingSystem": "Linux",
        "downloadUrl": "https://github.com/eventschedule/eventschedule",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free and open source under the AAL license"
        },
        "featureList": [
            "Multi-tenant customer subdomains",
            "Stripe subscription billing",
            "Free, Pro, and Enterprise plan tiers with feature gating",
            "Configurable trial length",
            "White-label branding",
            "Ticketing with QR check-in",
            "REST API and webhooks"
        ],
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    @php
        $howToSteps = [
            ['name' => 'Deploy the platform', 'text' => 'Install Event Schedule on your own server with Docker or the Softaculous one-click installer, then point wildcard DNS at it so every customer can get a subdomain.'],
            ['name' => 'Apply your branding', 'text' => 'Set your app name, upload light and dark logos, and connect your own domain so the platform runs entirely under your brand.'],
            ['name' => 'Connect Stripe and set your prices', 'text' => 'Connect your Stripe account, create prices for your Pro and Enterprise tiers, set the trial length with TRIAL_DAYS, and open sign-ups.'],
        ];
    @endphp
    <x-seo.howto-schema
        name="How to Launch a White-Label Ticketing SaaS"
        description="Deploy the open source Event Schedule platform, brand it, and start charging your own customers in three steps."
        :steps="$howToSteps" />
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           /saas page theme: "The Stack" - own every layer.
           Blueprint annotation kit, isometric ownership stack, and an
           amber accent reserved exclusively for money. Shared es-*
           primitives live in marketing.css; everything here is
           page-exclusive.
           ============================================================== */

        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-saas {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-saas {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Dark-in-both-modes panels need the bright variant in light mode too */
        .es-finale-panel .text-gradient-saas,
        .es-band-dark .text-gradient-saas {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Money rule: every dollar amount on the page is amber */
        .es-money { color: #d97706; }
        .dark .es-money { color: #fbbf24; }

        /* Blueprint mono tag */
        .es-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.65rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #2563eb;
        }
        .dark .es-tag { color: #60a5fa; }

        /* Viewfinder corner brackets */
        .es-corners::before,
        .es-corners::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            pointer-events: none;
            border: 0 solid rgba(37, 99, 235, 0.45);
            z-index: 2;
        }
        .es-corners::before {
            top: 10px;
            inset-inline-start: 10px;
            border-top-width: 1.5px;
            border-inline-start-width: 1.5px;
            border-start-start-radius: 4px;
        }
        .es-corners::after {
            bottom: 10px;
            inset-inline-end: 10px;
            border-bottom-width: 1.5px;
            border-inline-end-width: 1.5px;
            border-end-end-radius: 4px;
        }
        .dark .es-corners::before,
        .dark .es-corners::after { border-color: rgba(96, 165, 250, 0.4); }

        /* Marching-ants dashed connectors */
        .es-ants {
            stroke-dasharray: 5 7;
            animation: es-ants 1.6s linear infinite;
        }
        @keyframes es-ants { to { stroke-dashoffset: -12; } }

        /* Tenant tiles (customers on your platform) */
        .es-tenant {
            flex: 0 0 auto;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.7), rgba(14, 165, 233, 0.35));
            animation: es-tenant-pulse var(--tn-dur, 3s) ease-in-out infinite;
            animation-delay: var(--tn-delay, 0s);
        }
        @keyframes es-tenant-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.86); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        /* --------------------------------------------------------------
           Hero: the isometric ownership stack
           -------------------------------------------------------------- */

        .es-iso {
            display: grid;
            gap: 0.75rem;
        }
        .es-plane { position: relative; }
        .es-stack-bob { animation: es-stack-bob 7s ease-in-out infinite; }
        @keyframes es-stack-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @media (min-width: 1024px) {
            .es-stack-stage { min-height: 560px; }
            .es-iso {
                display: block;
                width: 330px;
                height: 205px;
                transform-style: preserve-3d;
                transform: rotateX(55deg) rotateZ(-45deg);
            }
            .es-plane {
                position: absolute;
                inset: 0;
                transform: translateZ(var(--z, 0px));
                transition:
                    transform 0.8s cubic-bezier(0.22, 1, 0.36, 1) calc(var(--n, 0) * 0.1s),
                    opacity 0.6s ease calc(var(--n, 0) * 0.1s);
                box-shadow: 0 24px 48px -20px rgba(2, 6, 23, 0.35);
            }
            html.es-anim .es-stack:not(.is-revealed) .es-plane {
                transform: translateZ(0);
                opacity: 0;
            }
            .es-stack:hover .es-plane { transform: translateZ(calc(var(--z, 0px) * 1.55)); }
        }

        /* --------------------------------------------------------------
           Machinery bento devices
           -------------------------------------------------------------- */

        /* Subdomains that type themselves */
        .es-sub-type {
            position: relative;
            display: inline-block;
            width: 24ch;
            height: 1.3em;
            vertical-align: bottom;
        }
        .es-sub-type span {
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            display: inline-block;
            max-width: 0;
            overflow: hidden;
            white-space: nowrap;
            border-inline-end: 2px solid #0ea5e9;
            opacity: 0;
            animation: es-type 9s steps(26, end) infinite;
            animation-delay: var(--w, 0s);
        }
        .dark .es-sub-type span { border-color: #38bdf8; }
        @keyframes es-type {
            0% { max-width: 0; opacity: 1; }
            14% { max-width: 26ch; opacity: 1; }
            30% { max-width: 26ch; opacity: 1; }
            32% { max-width: 26ch; opacity: 0; }
            33%, 100% { max-width: 0; opacity: 0; }
        }

        /* Plan-tier matrix: Pro column lights up on card hover */
        .es-tier-pro { transition: background-color 0.35s ease, box-shadow 0.35s ease; }
        .es-bento:hover .es-tier-pro {
            background: rgba(37, 99, 235, 0.08);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.35);
        }
        .dark .es-bento:hover .es-tier-pro {
            background: rgba(96, 165, 250, 0.1);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.4);
        }

        /* Trial countdown ring (donut via mask so any card bg works) */
        @property --p {
            syntax: '<percentage>';
            inherits: false;
            initial-value: 15%;
        }
        .es-trial-ring {
            background: conic-gradient(#f59e0b var(--p, 15%), rgba(245, 158, 11, 0.16) 0);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 8px), #000 calc(100% - 7px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 8px), #000 calc(100% - 7px));
            animation: es-trial 7s ease-in-out infinite;
        }
        @keyframes es-trial {
            0%, 100% { --p: 15%; }
            50% { --p: 100%; }
        }

        /* Billing flow: amber dots traveling from customers to your Stripe */
        .es-flow-dot {
            position: absolute;
            top: 50%;
            left: 0;
            width: 8px;
            height: 8px;
            margin-top: -4px;
            border-radius: 9999px;
            background: #f59e0b;
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.9);
            opacity: 0;
            animation: es-flow 3.2s ease-in-out infinite;
            animation-delay: var(--fd, 0s);
        }
        @keyframes es-flow {
            0% { left: 0; opacity: 0; }
            12% { opacity: 1; }
            58% { left: calc(100% - 8px); opacity: 1; }
            70% { left: calc(100% - 8px); opacity: 0; }
            100% { left: calc(100% - 8px); opacity: 0; }
        }

        /* White-label split tile: light and dark branding in one card */
        .es-split-dark {
            position: absolute;
            inset: 0;
            background: #0f0f14;
            clip-path: polygon(55% 0, 100% 0, 100% 100%, 45% 100%);
            transition: clip-path 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .es-bento:hover .es-split-dark { clip-path: polygon(45% 0, 100% 0, 100% 100%, 35% 100%); }

        /* --------------------------------------------------------------
           Revenue calculator
           -------------------------------------------------------------- */

        .es-range {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #f59e0b var(--fill, 50%), rgba(2, 6, 23, 0.08) var(--fill, 50%));
            cursor: pointer;
        }
        .dark .es-range {
            background: linear-gradient(90deg, #fbbf24 var(--fill, 50%), rgba(255, 255, 255, 0.12) var(--fill, 50%));
        }
        .es-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #fff;
            border: 2px solid #f59e0b;
            box-shadow: 0 2px 10px rgba(245, 158, 11, 0.35);
            cursor: grab;
        }
        .es-range::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #fff;
            border: 2px solid #f59e0b;
            box-shadow: 0 2px 10px rgba(245, 158, 11, 0.35);
            cursor: grab;
        }
        .dark .es-range::-webkit-slider-thumb { background: #0f0f14; }
        .dark .es-range::-moz-range-thumb { background: #0f0f14; }
        .es-range::-moz-range-track { background: transparent; }
        .es-range:focus-visible {
            outline: 2px solid #f59e0b;
            outline-offset: 4px;
        }

        /* Outlined zero: the whole platform fee */
        .es-ghost {
            color: transparent;
            -webkit-text-stroke: 2px rgba(217, 119, 6, 0.85);
        }
        .dark .es-ghost { -webkit-text-stroke-color: rgba(251, 191, 36, 0.8); }

        /* Horizontal comparison bars grow when the panel reveals */
        .es-hbar {
            transform: scaleX(0.04);
            transform-origin: left;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--hd, 0.3s);
        }
        [data-reveal].is-revealed .es-hbar,
        html:not(.es-anim) .es-hbar { transform: scaleX(1); }

        /* --------------------------------------------------------------
           The catch: backlink loupe
           -------------------------------------------------------------- */

        .es-loupe {
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 9999px;
            border: 2px solid rgba(245, 158, 11, 0.9);
            box-shadow: 0 0 0 6px rgba(251, 191, 36, 0.15), 0 20px 40px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            background: #fff;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        .es-catch-mock:hover .es-loupe {
            opacity: 1;
            animation: es-loupe-drift 5s ease-in-out infinite;
        }
        @keyframes es-loupe-drift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-8px, -6px); }
        }

        /* --------------------------------------------------------------
           Launch sequence: pinned story (same engine as the homepage)
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
            width: 2px;
            height: 1.1em;
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

        /* Terminal cursor (paths section homage to /selfhost) */
        .es-cursor {
            display: inline-block;
            width: 8px;
            height: 1.05em;
            vertical-align: text-bottom;
            background: #34d399;
            animation: es-caret 1.1s step-end infinite;
        }

        /* --------------------------------------------------------------
           Federation constellation
           -------------------------------------------------------------- */

        .es-node-pulse {
            animation: es-node-pulse 3.4s ease-in-out infinite;
            animation-delay: var(--np, 0s);
        }
        @keyframes es-node-pulse {
            0%, 100% { opacity: 0.45; }
            50% { opacity: 1; }
        }
        @supports (offset-path: path('M 0 0 H 10')) {
            .es-fed-dot {
                position: absolute;
                top: 0;
                left: 0;
                width: 8px;
                height: 8px;
                border-radius: 9999px;
                background: #38bdf8;
                box-shadow: 0 0 10px rgba(56, 189, 248, 0.9);
                opacity: 0;
                offset-rotate: 0deg;
                animation: es-fed 4.5s ease-in-out infinite;
                animation-delay: var(--fdl, 0s);
            }
            .es-fed-a { offset-path: path('M60 60 C 90 80, 130 110, 172 140'); }
            .es-fed-b { offset-path: path('M285 58 C 250 80, 210 110, 172 140'); }
            .es-fed-c { offset-path: path('M50 205 C 90 190, 130 165, 172 140'); }
        }
        @keyframes es-fed {
            0% { offset-distance: 0%; opacity: 0; }
            12% { opacity: 1; }
            82% { opacity: 1; }
            100% { offset-distance: 100%; opacity: 0; }
        }

        /* --------------------------------------------------------------
           Reduced motion: kill every page-exclusive animation (shared
           es-* kills live in marketing.css).
           -------------------------------------------------------------- */

        @media (prefers-reduced-motion: reduce) {
            .es-stack-bob,
            .es-ants,
            .es-sub-type span,
            .es-trial-ring,
            .es-flow-dot,
            .es-loupe,
            .es-fed-dot,
            .es-node-pulse,
            .es-caret,
            .es-cursor,
            .es-tenant,
            .animate-ping {
                animation: none !important;
            }
            .es-sub-type span { opacity: 0; }
            .es-sub-type span:first-child {
                max-width: none;
                opacity: 1;
                border-inline-end: 0;
            }
            .es-tenant { opacity: 0.5; transform: none; }
            .es-flow-dot { opacity: 0; }
            .es-node-pulse { opacity: 1; }
            .es-plane,
            .es-split-dark,
            .es-tier-pro,
            .es-hbar { transition: none !important; }
            .es-hbar { transform: scaleX(1); }
            .es-pop { opacity: 1; transform: none; transition: none; }
            .es-scene { transition: none; }
            .es-scene-0 { opacity: 1; transform: none; }
            /* The scenes are absolutely stacked; without the scroll story only
               the first one should show. */
            .es-scene-1,
            .es-scene-2 { display: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero: the ownership stack                                 -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 scroll-mt-24 dark:bg-[#0a0a0f]">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-spot absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-12 lg:gap-8">
                <!-- Copy -->
                <div class="text-center lg:col-span-6 lg:text-left rtl:lg:text-right">
                    <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                        </span>
                        <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Free white-label platform</span>
                    </div>

                    <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-6xl xl:text-7xl">
                        <span class="es-mask"><span class="es-mask-line">Launch your own</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">ticketing SaaS.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-saas">Own every layer.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 mx-auto mb-8 max-w-xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl lg:mx-0">
                        Event Schedule is a free, open source white-label ticketing platform with the multi-tenant SaaS layer built in. Selfhost it under your brand: your servers, your Stripe, your prices, and 100% of what your customers pay stays yours.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row lg:justify-start">
                        <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            View on GitHub
                        </a>
                        <a href="{{ route('marketing.docs.saas.setup') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Read the Setup Guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <ul class="es-fade-up es-d-4 mt-8 flex flex-wrap items-center justify-center gap-2 lg:justify-start">
                        @foreach (['100% open source', 'No per-ticket fees', 'Unlimited customers', 'All Enterprise features unlocked'] as $chip)
                            <li class="inline-flex items-center gap-1.5 rounded-full glass px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                <svg aria-hidden="true" class="h-3.5 w-3.5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $chip }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- The ownership stack -->
                <div class="relative lg:col-span-6">
                    <p class="sr-only">Illustration of the platform stack you own: your brand on top, customer subdomains beneath it, subscription billing under that, and your own server at the base.</p>
                    <div class="es-stack-stage relative flex items-center justify-center" aria-hidden="true">
                        <!-- Ground shadow -->
                        <div class="absolute left-1/2 top-[64%] hidden h-24 w-80 -translate-x-1/2 rounded-full bg-blue-500/20 blur-3xl lg:block"></div>

                        <!-- Legend (desktop) -->
                        <div class="absolute top-1/2 z-10 hidden -translate-y-1/2 flex-col gap-6 ltr:left-0 rtl:right-0 lg:flex">
                            @foreach ([['01', 'Brand'], ['02', 'Tenants'], ['03', 'Billing'], ['04', 'Infra']] as $leg)
                                <div class="flex items-center gap-2">
                                    <span class="es-tag">{{ $leg[0] }} · {{ $leg[1] }}</span>
                                    <svg class="text-blue-400/70 dark:text-blue-500/50" width="42" height="2" fill="none"><path class="es-ants" d="M0 1 H42" stroke="currentColor" stroke-width="1.5"/></svg>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-stack-bob w-full max-w-md lg:w-auto lg:max-w-none">
                            <div class="es-stack relative" data-reveal>
                                <div class="es-iso relative mx-auto">
                                    <!-- Plane 1: your brand -->
                                    <div class="es-plane rounded-2xl border border-blue-200 bg-white/95 p-4 backdrop-blur dark:border-blue-500/30 dark:bg-[#101016]/95" style="--z: 108px; --n: 3;">
                                        <div class="es-tag mb-2 lg:hidden">01 · Your brand</div>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-black text-white">TP</span>
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-bold text-gray-900 dark:text-white">TicketPilot</div>
                                                <div class="truncate font-mono text-[11px] text-gray-500 dark:text-gray-400">yourdomain.com</div>
                                            </div>
                                            <span class="ml-auto rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Your app</span>
                                        </div>
                                        <div class="mt-3 flex gap-1.5">
                                            <span class="h-1.5 w-16 rounded-full bg-gradient-to-r from-blue-500 to-sky-400"></span>
                                            <span class="h-1.5 w-8 rounded-full bg-gray-200 dark:bg-white/10"></span>
                                            <span class="h-1.5 w-12 rounded-full bg-gray-200 dark:bg-white/10"></span>
                                        </div>
                                    </div>

                                    <!-- Plane 2: customers -->
                                    <div class="es-plane rounded-2xl border border-gray-200 bg-white/95 p-4 backdrop-blur dark:border-white/10 dark:bg-[#101016]/95" style="--z: 72px; --n: 2;">
                                        <div class="es-tag mb-2 lg:hidden">02 · Customers</div>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach ([['acme.', 0], ['venue.', 1], ['comedy.', 2], ['gigs.', 3], ['expo.', 4], ['+ next', 5]] as $tenant)
                                                <div class="flex items-center gap-1.5 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5">
                                                    <span class="es-tenant !h-2.5 !w-2.5 !rounded" style="--tn-dur: {{ 2.6 + ($tenant[1] % 4) * 0.5 }}s; --tn-delay: {{ ($tenant[1] % 6) * 0.3 }}s;"></span>
                                                    <span class="truncate font-mono text-[10px] text-gray-600 dark:text-gray-300">{{ $tenant[0] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Plane 3: billing -->
                                    <div class="es-plane rounded-2xl border border-amber-200 bg-white/95 p-4 backdrop-blur dark:border-amber-500/30 dark:bg-[#101016]/95" style="--z: 36px; --n: 1;">
                                        <div class="es-tag mb-2 lg:hidden">03 · Billing</div>
                                        <div class="flex items-center justify-between gap-2">
                                            @foreach ([['Free', '$0'], ['Pro', '$29'], ['Ent', '$99']] as $tier)
                                                <div class="flex-1 rounded-xl border border-gray-200 px-2 py-2 text-center dark:border-white/10">
                                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $tier[0] }}</div>
                                                    <div class="es-money text-sm font-black">{{ $tier[1] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-2 text-center font-mono text-[10px] text-gray-400 dark:text-gray-500">prices you set · your Stripe</div>
                                    </div>

                                    <!-- Plane 4: your server -->
                                    <div class="es-plane rounded-2xl border border-white/10 bg-[#0b0f19] p-4" style="--z: 0px; --n: 0;">
                                        <div class="es-tag mb-2 !text-sky-400 lg:hidden">04 · Your server</div>
                                        <div class="flex items-center gap-3">
                                            <span class="relative flex h-2.5 w-2.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                            </span>
                                            <span class="font-mono text-xs text-gray-300">$ ./deploy · live</span>
                                            <span class="ml-auto rounded-full border border-white/15 px-2.5 py-1 font-mono text-[10px] text-gray-400">your infra</span>
                                        </div>
                                        <div class="mt-3 flex gap-1.5">
                                            <span class="h-1.5 w-10 rounded-full bg-emerald-500/50"></span>
                                            <span class="h-1.5 w-16 rounded-full bg-white/10"></span>
                                            <span class="h-1.5 w-6 rounded-full bg-white/10"></span>
                                        </div>
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
    <!-- 2. Machinery: everything already built                       -->
    <!-- ============================================================ -->
    <section id="machinery" class="border-t border-gray-200 bg-gray-50 py-24 scroll-mt-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                    Already Built
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Skip a year of <span class="text-gradient-saas">platform engineering</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">The multi-tenant machinery that takes the longest to build is the part you get on day one.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-12" data-reveal-group="80">
                <!-- Per-customer subdomains -->
                <div class="es-bento group relative lg:col-span-7" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-corners relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-tag mb-3">Module 01 · Provisioning</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Per-customer subdomains</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Every customer gets their own address the moment they sign up. Wildcard DNS in, tenant routing handled.</p>
                        <div class="mt-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <span class="flex gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#FF5F57]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#FEBC2E]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#28C840]"></span>
                                </span>
                                <div class="flex-1 overflow-hidden rounded-lg bg-white px-3 py-2 font-mono text-xs text-gray-700 shadow-sm dark:bg-white/10 dark:text-gray-200">
                                    <span class="text-gray-400 dark:text-gray-500">https://</span><span class="es-sub-type"><span>acme.yourdomain.com</span><span style="--w: 3s;">blues-bar.yourdomain.com</span><span style="--w: 6s;">comedy.yourdomain.com</span></span>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-3" style="mask-image: linear-gradient(to right, black 75%, transparent);">
                                @for ($i = 0; $i < 12; $i++)
                                    <span class="es-tenant" style="--tn-dur: {{ 2.6 + ($i % 5) * 0.4 }}s; --tn-delay: {{ ($i % 8) * 0.26 }}s;"></span>
                                @endfor
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Plan tiers -->
                <div class="es-bento group relative lg:col-span-5" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-corners relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-tag mb-3">Module 02 · Plans</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Plan tiers you control</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Free, Pro, and Enterprise are built in, with features gated per tier. You decide what each tier includes and costs.</p>
                        <div class="mt-auto overflow-hidden rounded-2xl border border-gray-200 text-sm dark:border-white/10" aria-hidden="true">
                            <div class="grid grid-cols-[1fr_2.5rem_2.5rem_2.5rem] items-center gap-y-1 p-3">
                                <span></span>
                                <span class="text-center text-[11px] font-semibold text-gray-400 dark:text-gray-500">Free</span>
                                <span class="es-tier-pro rounded-md py-0.5 text-center text-[11px] font-bold text-blue-600 dark:text-blue-400">Pro</span>
                                <span class="text-center text-[11px] font-semibold text-gray-400 dark:text-gray-500">Ent</span>
                                @foreach ([['Schedule pages', true, true, true], ['Ticketing', false, true, true], ['API + webhooks', false, true, true], ['Custom domains', false, false, true]] as $row)
                                    <span class="truncate py-1.5 text-xs text-gray-600 dark:text-gray-300">{{ $row[0] }}</span>
                                    @foreach ([1, 2, 3] as $col)
                                        <span class="{{ $col === 2 ? 'es-tier-pro rounded-md' : '' }} flex justify-center py-1.5">
                                            @if ($row[$col])
                                                <span class="h-2 w-2 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400"></span>
                                            @else
                                                <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-white/20"></span>
                                            @endif
                                        </span>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Trials -->
                <div class="es-bento group relative md:col-span-1 lg:col-span-4" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-corners relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-tag mb-3">Module 03 · Trials</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Trials you control</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">One environment variable sets the trial length for your whole platform. Set it to 0 for no trial.</p>
                        <div class="mt-auto flex items-center justify-between gap-4" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 dark:border-white/10 dark:bg-[#0f0f14]">
                                <code class="font-mono text-sm text-gray-600 dark:text-gray-300"><span class="text-blue-500 dark:text-blue-400">TRIAL_DAYS</span>=<span class="text-emerald-500 dark:text-emerald-400">14</span></code>
                            </div>
                            <div class="relative flex h-24 w-24 shrink-0 items-center justify-center">
                                <div class="es-trial-ring absolute inset-0 rounded-full"></div>
                                <div class="text-center">
                                    <div class="text-xl font-black text-gray-900 dark:text-white">14</div>
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">days</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Billing -->
                <div class="es-bento group relative md:col-span-1 lg:col-span-4" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-corners relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-tag mb-3">Module 04 · Billing</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Subscription billing, wired</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Recurring billing runs through your own Stripe account. You create the products, the prices, and the currencies.</p>
                        <div class="mt-auto flex items-center rounded-2xl border border-gray-200 bg-gray-50 px-4 py-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <span class="rounded-full bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Customers</span>
                            <div class="relative mx-3 h-px flex-1 bg-gray-300 dark:bg-white/15">
                                <span class="es-flow-dot" style="--fd: 0s;"></span>
                                <span class="es-flow-dot" style="--fd: 1.1s;"></span>
                                <span class="es-flow-dot" style="--fd: 2.2s;"></span>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Your Stripe</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- White-label branding -->
                <div class="es-bento group relative md:col-span-2 lg:col-span-4" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-corners relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-tag mb-3">Module 05 · Branding</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">White-label branding</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Your app name, your light and dark logos, your domain. Customers see your brand everywhere.</p>
                        <div class="relative mt-auto h-24 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10" aria-hidden="true">
                            <div class="absolute inset-0 flex items-center ltr:justify-start ltr:pl-5 rtl:justify-end rtl:pr-5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 text-[10px] font-black text-white">TP</span>
                                    <span class="text-sm font-bold text-gray-900">TicketPilot</span>
                                </div>
                            </div>
                            <div class="es-split-dark">
                                <div class="absolute inset-0 flex items-center ltr:justify-end ltr:pr-5 rtl:justify-start rtl:pl-5">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-sky-400 to-cyan-300 text-[10px] font-black text-[#0f0f14]">TP</span>
                                        <span class="text-sm font-bold text-white">TicketPilot</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- No limits strip -->
                <div class="relative md:col-span-2 lg:col-span-12" data-reveal>
                    <div class="es-corners relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="es-tag">Module 06 · Scale</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Unlimited customers, unlimited schedules, and every Enterprise feature unlocked on your install.</p>
                        </div>
                        <div class="es-marquee es-marquee-mask" data-marquee="1" aria-hidden="true">
                            <div class="es-marquee-track">
                                @for ($copy = 0; $copy < 2; $copy++)
                                    <div class="flex gap-3.5" @if ($copy === 1) aria-hidden="true" @endif>
                                        @foreach ([['acme', 'Pro'], ['blues-bar', 'Enterprise'], ['startup', 'Free'], ['comedy-cellar', 'Pro'], ['gallery', 'Trial'], ['bookclub', 'Free'], ['expo', 'Enterprise'], ['openmic', 'Pro'], ['makerspace', 'Trial'], ['warehouse', 'Pro']] as $chip)
                                            <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-gray-200 bg-gray-50 px-4 py-2 font-mono text-xs text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                                                {{ $chip[0] }}.yourdomain.com
                                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">{{ $chip[1] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The math: revenue calculator                              -->
    <!-- ============================================================ -->
    <section id="revenue" class="bg-white py-24 scroll-mt-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-3 py-1 text-sm font-medium text-amber-700 dark:text-amber-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    The Business Model
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>The math works <span class="text-gradient-saas">in your favor</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">No license fees and no revenue share means what your customers pay is yours. Your real costs are a server and Stripe processing.</p>
            </div>

            <div id="es-calc" class="es-corners relative mx-auto max-w-5xl overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-10 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Controls -->
                    <div class="flex flex-col justify-center gap-10">
                        <div>
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <label for="es-r-customers" class="font-semibold text-gray-900 dark:text-white">Customers on your platform</label>
                                <span id="es-out-customers" class="rounded-full bg-gray-100 px-3 py-1 font-mono text-sm font-bold tabular-nums text-gray-700 dark:bg-white/10 dark:text-gray-200">25</span>
                            </div>
                            <input type="range" id="es-r-customers" class="es-range" min="1" max="500" step="1" value="25" style="--fill: 4.8%;">
                            <div class="mt-2 flex justify-between font-mono text-[11px] text-gray-400 dark:text-gray-500"><span>1</span><span>500</span></div>
                        </div>
                        <div>
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <label for="es-r-price" class="font-semibold text-gray-900 dark:text-white">Your price per customer per month</label>
                                <span id="es-out-price" class="es-money rounded-full bg-amber-50 px-3 py-1 font-mono text-sm font-bold tabular-nums dark:bg-amber-500/10">$29</span>
                            </div>
                            <input type="range" id="es-r-price" class="es-range" min="5" max="199" step="1" value="29" style="--fill: 12.4%;">
                            <div class="mt-2 flex justify-between font-mono text-[11px] text-gray-400 dark:text-gray-500"><span>$5</span><span>$199</span></div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Slide to match your niche and your pricing. The defaults are just a starting point.</p>
                    </div>

                    <!-- Results -->
                    <div aria-live="polite" class="flex flex-col gap-6">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Your monthly revenue</div>
                            <div id="es-out-month" class="es-money mt-1 text-5xl font-black tabular-nums sm:text-6xl">$725</div>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">That is <span id="es-out-year" class="es-money font-bold tabular-nums">$8,700</span> per year.</div>
                        </div>

                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-6 dark:border-amber-500/25 dark:bg-amber-500/[0.06]">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">What Event Schedule takes</div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">No license fee, no revenue share, no per-ticket fees.</div>
                            </div>
                            <div class="es-ghost shrink-0 text-6xl font-black" aria-hidden="true">$0</div>
                            <span class="sr-only">Zero dollars.</span>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-xs font-semibold text-gray-600 dark:text-gray-300">
                                <span>Your platform</span><span class="es-money">100% yours</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="es-hbar h-full w-full rounded-full bg-gradient-to-r from-amber-500 to-amber-400" style="--hd: 0.3s;"></div>
                            </div>
                            <div class="mb-2 mt-4 flex items-center justify-between text-xs font-semibold text-gray-600 dark:text-gray-300">
                                <span>A typical 10-30% reseller share</span>
                                <span class="text-rose-600 dark:text-rose-400">hand over <span id="es-out-cut-low" class="tabular-nums">$870</span> to <span id="es-out-cut-high" class="tabular-nums">$2,610</span>/yr</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="es-hbar h-full w-[30%] rounded-full bg-gradient-to-r from-rose-500 to-rose-400" style="--hd: 0.5s;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-8 border-t border-gray-100 pt-5 text-xs text-gray-400 dark:border-white/5 dark:text-gray-500">Estimates for illustration. Stripe processing fees apply per transaction and vary by country. Your results depend on your pricing and your customers.</p>
            </div>

            <!-- Illustrative activity ticker -->
            <div class="mx-auto mt-10 max-w-5xl" data-reveal>
                <div class="es-marquee es-marquee-mask" data-marquee="-1" aria-hidden="true">
                    <div class="es-marquee-track">
                        @for ($copy = 0; $copy < 2; $copy++)
                            <div class="flex gap-3.5" @if ($copy === 1) aria-hidden="true" @endif>
                                @foreach ([['acme upgraded to Pro', '+$29/mo'], ['2 trials started', ''], ['blues-bar renewed Enterprise', '+$99/mo'], ['comedy-cellar sold 40 tickets', ''], ['gallery subscribed to Pro', '+$29/mo'], ['expo added 3 team members', ''], ['makerspace converted from trial', '+$29/mo']] as $tick)
                                    <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-xs text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        {{ $tick[0] }}
                                        @if ($tick[1] !== '')
                                            <span class="es-money font-mono font-bold">{{ $tick[1] }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">A feed you could be running.</p>
            </div>

            <p class="mx-auto mt-10 max-w-3xl text-center text-lg text-gray-500 dark:text-gray-400" data-reveal>This is the technology half of starting an online ticketing business. The other half is finding customers, and you can spend your time there because the platform is already built.</p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Build vs rent vs own                                      -->
    <!-- ============================================================ -->
    <section id="compare" class="border-t border-gray-200 bg-gray-50 py-24 scroll-mt-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-sky-500/20 px-3 py-1 text-sm font-medium text-sky-700 dark:text-sky-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    The Decision
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Build it, rent it, or <span class="text-gradient-saas">own it</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Three ways into the ticketing business. Only one gives you both speed and ownership.</p>
            </div>

            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="100">
                <!-- Build -->
                <div class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]" data-reveal>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Build from scratch</h3>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">12+ months</span>
                    </div>
                    <ul class="mb-6 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        @foreach (['Multi-tenancy, billing, ticketing, check-in, calendar sync, GDPR: all on you', 'Every feature request lands on your backlog', 'Total control, eventually'] as $li)
                            <li class="flex items-start gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 12h14" /></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-auto border-t border-gray-100 pt-4 text-sm font-medium text-gray-500 dark:border-white/5 dark:text-gray-400">Right if the platform itself is your moat.</p>
                </div>

                <!-- Rent -->
                <div class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]" data-reveal>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Reseller programs</h3>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">Days to launch</span>
                    </div>
                    <ul class="mb-6 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        @foreach (['Typically a revenue share plus per-ticket fees, forever', 'No code access, and it is their roadmap', 'Your brand on a platform you can lose'] as $li)
                            <li class="flex items-start gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-auto border-t border-gray-100 pt-4 text-sm font-medium text-gray-500 dark:border-white/5 dark:text-gray-400">Fast, but you are renting your own business.</p>
                </div>

                <!-- Own (highlighted) -->
                <div class="relative flex flex-col rounded-3xl border border-blue-300 bg-white p-8 ring-2 ring-blue-500/30 dark:border-blue-500/40 dark:bg-white/[0.06] dark:ring-blue-400/25" data-reveal>
                    <div class="absolute -top-3 ltr:right-6 rtl:left-6 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow-lg shadow-blue-500/30">Own it</div>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Selfhost Event Schedule</h3>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Days to launch</span>
                    </div>
                    <ul class="mb-6 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        @php
                            $ownBullets = [
                                '$0 license, no revenue share, no per-ticket fees',
                                'Full code access, your data in your MySQL database',
                                'Multi-tenant billing and white-label built in',
                            ];
                        @endphp
                        @foreach ($ownBullets as $li)
                            <li class="flex items-start gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-sm text-amber-800 dark:border-amber-500/25 dark:bg-amber-500/[0.08] dark:text-amber-200">The trade: you run the servers and keep a small attribution link.</p>
                    <p class="mt-auto border-t border-gray-100 pt-4 text-sm font-medium text-gray-600 dark:border-white/5 dark:text-gray-300">Speed and ownership. <x-link href="{{ route('marketing.selfhost') }}">See how selfhosting works</x-link></p>
                </div>
            </div>

            <p class="mx-auto mt-10 max-w-3xl text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>As far as we know, Event Schedule is one of the few open source ticketing platforms with the multi-tenant subscription layer built in. Don't take our word for it: <x-link href="https://github.com/eventschedule/eventschedule" target="_blank">read the code</x-link></p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The catch, at actual size                                 -->
    <!-- ============================================================ -->
    <section id="catch" class="bg-white px-2 py-16 scroll-mt-24 sm:px-4 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-14 shadow-2xl shadow-blue-500/10 sm:px-12 lg:py-20" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(37, 99, 235, 0.35), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-20"></div>
                </div>

                <div class="relative z-10 grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-sm font-medium text-gray-200">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            The Catch
                        </div>
                        <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl">Why is it <span class="text-gradient-saas">free?</span></h2>
                        <p class="mb-8 text-lg text-gray-300">No expiring trial, no locked features, no surprise pricing later. We ask for two things instead.</p>

                        <div class="mb-8 space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/20">
                                    <svg aria-hidden="true" class="h-5 w-5 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">The AAL license</h3>
                                    <p class="text-sm text-gray-400">Event Schedule is open source under the Attribution Assurance License. Use it commercially at no cost; just keep the attribution intact.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20">
                                    <svg aria-hidden="true" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">A small backlink</h3>
                                    <p class="text-sm text-gray-400">Public schedule pages carry a small, discreet link back to eventschedule.com. That link is how the project grows, which keeps the software maintained for everyone.</p>
                                </div>
                            </div>
                        </div>

                        @include('marketing.partials.github-star-badge')

                        <div class="mb-6 flex flex-wrap gap-2" aria-hidden="true">
                            @foreach (['AAL Licensed', '100% Open Source', 'Free Forever'] as $chip)
                                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-gray-200">{{ $chip }}</span>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/10">
                                <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                Contribute on GitHub
                            </a>
                            <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/10">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                                Join the Discussions
                            </a>
                        </div>
                        <p class="mt-6 text-sm text-gray-400">Read more about <a href="{{ route('marketing.open_source') }}" class="text-sky-300 underline decoration-sky-300/40 underline-offset-2 hover:text-sky-200">the open source project</a> or the full <a href="{{ route('marketing.self_hosting_terms') }}" class="text-sky-300 underline decoration-sky-300/40 underline-offset-2 hover:text-sky-200">self-hosting terms</a>.</p>
                    </div>

                    <!-- The loupe: the catch shown at actual size -->
                    <div class="es-catch-mock relative mx-auto w-full max-w-md" aria-hidden="true">
                        <div class="rounded-2xl bg-white p-5 shadow-2xl">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-black text-white">TP</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">The Comedy Cellar</div>
                                    <div class="font-mono text-[11px] text-gray-500">comedy.yourdomain.com</div>
                                </div>
                                <span class="ml-auto rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-3 py-1 text-[10px] font-semibold text-white">Follow</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach ([['Aug', '02', 'Open Mic Night', '8:00 PM · Free'], ['Aug', '09', 'Headliner Showcase', '9:00 PM · $22'], ['Aug', '16', 'Improv Jam', '7:30 PM · $15']] as $ev)
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-2.5">
                                        <span class="w-11 shrink-0 rounded-lg bg-blue-50 py-1 text-center"><span class="block text-[9px] font-bold uppercase text-blue-500">{{ $ev[0] }}</span><span class="block text-base font-black leading-none text-gray-900">{{ $ev[1] }}</span></span>
                                        <div class="min-w-0"><div class="truncate text-sm font-semibold text-gray-900">{{ $ev[2] }}</div><div class="text-xs text-gray-500">{{ $ev[3] }}</div></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                                <span class="text-[10px] text-gray-400">© TicketPilot</span>
                                {{-- The real attribution shown on public schedule pages (see
                                     layouts/app-guest.blade.php): a small flat brand credit. --}}
                                <span id="es-backlink-chip" class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-1.5 py-0.5 text-[9px] font-medium text-gray-500 ring-1 ring-black/5">
                                    <span class="flex h-3 w-3 items-center justify-center rounded-[3px] bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-[6px] font-black leading-none text-white">ES</span>
                                    Event Schedule
                                </span>
                            </div>
                        </div>
                        <!-- Magnifier over the backlink credit (desktop hover) -->
                        <div class="es-loupe hidden bottom-[-28px] items-center justify-center ltr:right-[-18px] rtl:left-[-18px] lg:flex">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-2xl font-black text-white shadow-lg">ES</span>
                        </div>
                        <p class="mt-4 text-center text-sm text-gray-400">Shown at actual size. That is the whole catch.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Launch sequence: live in three steps                      -->
    <!-- ============================================================ -->
    <section id="launch" class="es-steps relative border-t border-gray-200 bg-gray-50 scroll-mt-24 dark:border-white/5 dark:bg-[#0f0f14]" data-scene="steps" data-active-step="all">
        <div class="es-steps-pin w-full py-24 lg:py-0">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Launch Plan</span>
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Live in <span class="text-gradient-saas">three steps</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                        Most of the work is choosing your niche and your prices, not writing code.
                    </p>
                </div>

                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <!-- Steps -->
                    <div class="relative">
                        <div class="absolute bottom-4 top-4 w-px bg-gray-200 ltr:left-6 rtl:right-6 dark:bg-white/10" aria-hidden="true">
                            <span class="es-steps-progress absolute inset-x-0 top-0 h-full bg-gradient-to-b from-[#4E81FA] via-[#0EA5E9] to-[#F59E0B]"></span>
                        </div>
                        <div class="space-y-12">
                            <div class="es-step es-step-0 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-400 text-xl font-bold text-white shadow-lg shadow-blue-500/30 ltr:left-0 rtl:right-0">1</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Deploy your platform</h3>
                                <p class="text-gray-600 dark:text-gray-400">Install with Docker or the Softaculous one-click installer and point wildcard DNS at your server. The <x-link href="{{ route('marketing.docs.saas.setup') }}">setup guide</x-link> covers every step, and the <x-link href="{{ route('marketing.selfhost') }}">selfhost page</x-link> compares install options.</p>
                            </div>
                            <div class="es-step es-step-1 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30 ltr:left-0 rtl:right-0">2</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Wire up the business</h3>
                                <p class="text-gray-600 dark:text-gray-400">Set your app name, upload light and dark logos, connect Stripe, and create the prices for your Pro and Enterprise tiers. One variable sets your trial length.</p>
                            </div>
                            <div class="es-step es-step-2 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-400 text-xl font-bold text-white shadow-lg shadow-amber-500/30 ltr:left-0 rtl:right-0">3</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Your first customer signs up</h3>
                                <p class="text-gray-600 dark:text-gray-400">They pick a subdomain, start a trial, and subscribe through your Stripe. From here on, growth is a sales problem, not an engineering one.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Device mock (desktop only) -->
                    <div class="relative hidden lg:block" aria-hidden="true">
                        <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-blue-500/10 via-transparent to-amber-500/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#101016]">
                            <div class="flex items-center gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                <span class="flex gap-1.5">
                                    <span class="h-3 w-3 rounded-full bg-[#FF5F57]"></span>
                                    <span class="h-3 w-3 rounded-full bg-[#FEBC2E]"></span>
                                    <span class="h-3 w-3 rounded-full bg-[#28C840]"></span>
                                </span>
                                <span class="mx-auto rounded-lg bg-white px-4 py-1 text-xs font-medium text-gray-500 shadow-sm dark:bg-white/10 dark:text-gray-400">
                                    <span class="es-type-url" data-full="customer.yourdomain.com">customer.yourdomain.com</span><span class="es-caret"></span>
                                </span>
                                <span class="w-14"></span>
                            </div>
                            <div class="relative h-[380px]">
                                <!-- Scene 1: deploy -->
                                <div class="es-scene es-scene-0 p-6">
                                    <div class="h-full rounded-xl bg-[#0b0f19] p-5 font-mono text-sm leading-8 text-gray-200">
                                        <div class="es-pop" style="--i: 0;"><span class="text-emerald-400">$</span> git clone eventschedule/eventschedule</div>
                                        <div class="es-pop" style="--i: 1;"><span class="text-emerald-400">$</span> docker compose up -d</div>
                                        <div class="es-pop text-gray-400" style="--i: 2;">Pulling images... done</div>
                                        <div class="es-pop text-gray-400" style="--i: 3;">Running migrations... done</div>
                                        <div class="es-pop" style="--i: 4;"><span class="text-emerald-400">✓</span> Platform live at <span class="text-sky-400">yourdomain.com</span></div>
                                    </div>
                                </div>
                                <!-- Scene 2: wire up the business -->
                                <div class="es-scene es-scene-1 flex flex-col items-center justify-center gap-5 p-6">
                                    <div class="es-pop w-full max-w-sm rounded-xl border border-gray-200 bg-gray-50 p-4 font-mono text-sm dark:border-white/10 dark:bg-white/5" style="--i: 0;">
                                        <div><span class="text-blue-500 dark:text-blue-400">APP_NAME</span>=<span class="text-gray-700 dark:text-gray-200">TicketPilot</span></div>
                                        <div><span class="text-blue-500 dark:text-blue-400">TRIAL_DAYS</span>=<span class="text-emerald-500 dark:text-emerald-400">14</span></div>
                                        <div><span class="text-blue-500 dark:text-blue-400">STRIPE_SECRET</span>=<span class="text-gray-500 dark:text-gray-400">sk_live_••••</span></div>
                                    </div>
                                    <div class="flex gap-3">
                                        @foreach ([['Free', '$0', 0], ['Pro', '$29', 1], ['Enterprise', '$99', 2]] as $tier)
                                            <div class="es-pop rounded-2xl border border-gray-200 px-5 py-3 text-center dark:border-white/10" style="--i: {{ $tier[2] + 1 }};">
                                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $tier[0] }}</div>
                                                <div class="es-money text-lg font-black">{{ $tier[1] }}<span class="text-xs font-semibold">/mo</span></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- Scene 3: first customer -->
                                <div class="es-scene es-scene-2 flex flex-col items-center justify-center gap-5 p-6">
                                    <div class="es-pop flex w-full max-w-sm items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-lg dark:border-white/10 dark:bg-[#15151c]" style="--i: 0;">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-sm font-black text-white">A</span>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">acme.yourdomain.com</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Trial started · 14 days</div>
                                        </div>
                                        <svg class="ml-auto h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div class="es-pop rounded-full border border-amber-200 bg-amber-50 px-5 py-2.5 font-mono text-sm font-bold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300" style="--i: 1;">+$0 today · $29/mo after trial</div>
                                    <div class="es-pop text-xs text-gray-400 dark:text-gray-500" style="--i: 2;">Straight into your Stripe account</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operator docs row (continuation of the launch section) -->
    <div class="bg-gray-50 pb-24 dark:bg-[#0f0f14]">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-3 px-4 sm:px-6 lg:px-8" data-reveal>
            <span class="es-tag">Operator docs</span>
            @foreach ([['SaaS setup guide', route('marketing.docs.saas.setup')], ['Custom domains for customers', route('marketing.docs.saas.custom_domains')], ['Twilio SMS setup', route('marketing.docs.saas.twilio')]] as $doc)
                <a href="{{ $doc[1] }}" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:border-blue-300 hover:text-blue-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:border-blue-500/40 dark:hover:text-blue-300">
                    {{ $doc[0] }}
                    <svg aria-hidden="true" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 7. What you're selling                                       -->
    <!-- ============================================================ -->
    <section id="product" class="bg-white py-24 scroll-mt-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-cyan-500/20 px-3 py-1 text-sm font-medium text-cyan-700 dark:text-cyan-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    For Your Customers
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>A product worth <span class="text-gradient-saas">subscribing to</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Each customer gets a complete scheduling and ticketing toolkit under your brand, on their own subdomain. Try both sides of it.</p>
            </div>

            <div class="mx-auto grid max-w-5xl gap-4 md:grid-cols-2" data-reveal-group="90">
                <!-- Admin portal -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10" aria-hidden="true">
                            <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                <span class="flex gap-1"><span class="h-2 w-2 rounded-full bg-[#FF5F57]"></span><span class="h-2 w-2 rounded-full bg-[#FEBC2E]"></span><span class="h-2 w-2 rounded-full bg-[#28C840]"></span></span>
                                <span class="mx-auto font-mono text-[10px] text-gray-400 dark:text-gray-500">admin · acme.yourdomain.com</span>
                            </div>
                            <div class="flex">
                                <div class="flex w-10 flex-col items-center gap-2 border-r border-gray-100 py-3 dark:border-white/5">
                                    @for ($i = 0; $i < 4; $i++)
                                        <span class="h-4 w-4 rounded-md {{ $i === 0 ? 'bg-gradient-to-br from-blue-500 to-cyan-400' : 'bg-gray-200 dark:bg-white/10' }}"></span>
                                    @endfor
                                </div>
                                <div class="flex-1 p-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Ticket sales</span>
                                        <span class="es-money font-mono text-xs font-bold">$1,240</span>
                                    </div>
                                    <div class="flex h-16 items-end gap-1.5">
                                        @foreach ([35, 55, 40, 75, 60, 100] as $j => $h)
                                            <div class="es-bar flex-1 rounded-t-sm bg-gradient-to-t from-blue-500 to-cyan-400" style="height: {{ $h }}%; --bd: {{ 0.3 + $j * 0.08 }}s;"></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 flex items-center gap-2 rounded-lg border border-gray-100 p-1.5 dark:border-white/5">
                                        <span class="h-1.5 w-14 rounded-full bg-gray-200 dark:bg-white/10"></span>
                                        <span class="ml-auto rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">On sale</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">The admin portal</h3>
                        <p class="mb-6 flex-grow text-sm text-gray-500 dark:text-gray-400">Where your customers create events, sell tickets through Stripe Connect, track sales, send newsletters, and check attendees in.</p>
                        <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white transition-colors hover:from-blue-500 hover:to-sky-500">
                            Open the Admin Demo
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Guest portal -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10" aria-hidden="true">
                            <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                <span class="flex gap-1"><span class="h-2 w-2 rounded-full bg-[#FF5F57]"></span><span class="h-2 w-2 rounded-full bg-[#FEBC2E]"></span><span class="h-2 w-2 rounded-full bg-[#28C840]"></span></span>
                                <span class="mx-auto font-mono text-[10px] text-gray-400 dark:text-gray-500">acme.yourdomain.com</span>
                            </div>
                            <div class="flex gap-3 p-3">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 text-[8px] font-black text-white">AC</span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Acme Live</span>
                                    </div>
                                    @foreach ([['Sep', '05', 'Rooftop Sessions'], ['Sep', '12', 'Fall Showcase']] as $ev)
                                        <div class="flex items-center gap-2 rounded-lg border border-gray-100 p-1.5 dark:border-white/5">
                                            <span class="w-8 shrink-0 rounded-md bg-blue-50 py-0.5 text-center dark:bg-blue-500/15"><span class="block text-[7px] font-bold uppercase text-blue-500">{{ $ev[0] }}</span><span class="block text-xs font-black leading-none text-gray-900 dark:text-white">{{ $ev[1] }}</span></span>
                                            <span class="truncate text-[10px] font-semibold text-gray-700 dark:text-gray-300">{{ $ev[2] }}</span>
                                            <span class="ml-auto rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-2 py-0.5 text-[8px] font-bold text-white">Buy</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="relative w-16 shrink-0 self-center overflow-hidden rounded-lg border border-gray-200 p-1.5 dark:border-white/10">
                                    <div class="grid grid-cols-5 gap-0.5">
                                        @for ($i = 0; $i < 25; $i++)
                                            <span class="aspect-square rounded-[1px] {{ in_array($i, [0, 1, 2, 4, 5, 8, 10, 12, 14, 16, 18, 20, 22, 24, 3, 9, 15, 21]) ? 'bg-gray-800 dark:bg-gray-200' : 'bg-transparent' }}"></span>
                                        @endfor
                                    </div>
                                    <div class="es-laser" aria-hidden="true"></div>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">The guest portal</h3>
                        <p class="mb-6 flex-grow text-sm text-gray-500 dark:text-gray-400">The public schedule their attendees see: browse events, buy tickets, RSVP, and scan in at the door with QR codes.</p>
                        <a href="https://simpsons.eventschedule.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white transition-colors hover:from-blue-500 hover:to-sky-500">
                            Open the Guest Demo
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <!-- Feature dock -->
            <div class="mx-auto mt-10 max-w-5xl" data-reveal>
                <div class="es-marquee es-marquee-mask" data-marquee="1" aria-hidden="true">
                    <div class="es-marquee-track">
                        @for ($copy = 0; $copy < 2; $copy++)
                            <div class="flex gap-3.5" @if ($copy === 1) aria-hidden="true" @endif>
                                @foreach ([
                                    ['Ticketing via Stripe Connect', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                                    ['QR check-in', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
                                    ['Calendar sync', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                    ['Newsletters', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                                    ['Analytics', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                                    ['Schedule pages', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                    ['Recurring events', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                    ['Embeds', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                                ] as $feat)
                                    <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-medium text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                                        <svg class="h-3.5 w-3.5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat[1] }}" /></svg>
                                        {{ $feat[0] }}
                                    </span>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">Every plan tier you sell is backed by the full event platform.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Federation (coming soon)                                  -->
    <!-- ============================================================ -->
    <section id="federation" class="border-t border-gray-200 bg-gray-50 py-24 scroll-mt-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-sky-500/20 px-3 py-1 text-sm font-medium text-sky-700 dark:text-sky-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Coming Soon
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl"><span class="text-gradient-saas">Federation</span> is on the way</h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400 sm:text-xl">An optional network that sends discovery traffic back to your platform.</p>
                    <p class="mb-6 text-gray-600 dark:text-gray-300">Operators will be able to share their customers' online events to the eventschedule.com listings. Every listing links back to the event on your platform: extra reach and SEO for your customers, opt-in and off by default.</p>
                    <ul class="space-y-3">
                        @foreach (['Discovery traffic flows to your installation', 'Your customers reach a wider audience', 'Completely optional, off by default'] as $li)
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Constellation -->
                <div class="relative mx-auto h-[280px] w-[340px] max-w-full" aria-hidden="true" data-reveal style="--reveal-delay: 0.1s;">
                    <svg viewBox="0 0 340 280" class="absolute inset-0 h-full w-full" fill="none">
                        <path class="es-ants stroke-blue-300 dark:stroke-blue-500/50" stroke-width="1.5" d="M60 60 C 90 80, 130 110, 172 140" />
                        <path class="es-ants stroke-sky-300 dark:stroke-sky-500/50" stroke-width="1.5" d="M285 58 C 250 80, 210 110, 172 140" />
                        <path class="es-ants stroke-cyan-300 dark:stroke-cyan-500/50" stroke-width="1.5" d="M50 205 C 90 190, 130 165, 172 140" />
                        <path class="es-ants stroke-blue-300 dark:stroke-blue-500/50" stroke-width="1.5" d="M160 246 C 165 215, 168 180, 172 140" />
                        <path class="es-ants stroke-sky-300 dark:stroke-sky-500/50" stroke-width="1.5" d="M300 195 C 260 180, 215 158, 172 140" />
                        <circle class="es-node-pulse fill-blue-400/70" style="--np: 0s;" cx="60" cy="60" r="8" />
                        <circle class="es-node-pulse fill-cyan-400/70" style="--np: 0.6s;" cx="50" cy="205" r="8" />
                        <circle class="es-node-pulse fill-blue-400/70" style="--np: 1.2s;" cx="160" cy="246" r="8" />
                        <circle class="es-node-pulse fill-sky-400/70" style="--np: 1.8s;" cx="300" cy="195" r="8" />
                        <circle class="fill-white stroke-amber-400 dark:fill-[#15151c]" stroke-width="2.5" cx="285" cy="58" r="11" />
                        <circle class="fill-white stroke-blue-500 dark:fill-[#15151c]" stroke-width="3" cx="172" cy="140" r="17" />
                        <circle class="fill-blue-500" cx="172" cy="140" r="7" />
                    </svg>
                    <div class="es-fed-dot es-fed-a hidden md:block" style="--fdl: 0s;"></div>
                    <div class="es-fed-dot es-fed-b hidden md:block" style="--fdl: 1.4s;"></div>
                    <div class="es-fed-dot es-fed-c hidden md:block" style="--fdl: 2.8s;"></div>
                    <span class="absolute left-1/2 top-[62%] -translate-x-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-2.5 py-1 text-[10px] font-semibold text-gray-600 shadow-sm dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">eventschedule.com</span>
                    <span class="absolute left-[76%] top-[9%] whitespace-nowrap rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">yourdomain.com</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Two ways to run it                                        -->
    <!-- ============================================================ -->
    <section id="paths" class="bg-white py-24 scroll-mt-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Two ways to <span class="text-gradient-saas">run it</span></h2>
            </div>
            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <!-- Selfhost for yourself -->
                <div class="flex flex-col rounded-3xl border border-emerald-200 bg-white p-8 dark:border-emerald-500/25 dark:bg-white/[0.04]" data-reveal>
                    <div class="mb-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400">Just want your own instance?</div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Selfhost for yourself</h3>
                    <p class="mb-5 text-gray-500 dark:text-gray-400">Run Event Schedule for your own events, with every Enterprise feature unlocked and zero platform fees.</p>
                    <div class="mb-6 rounded-xl bg-[#0b0f19] px-4 py-3 font-mono text-sm text-gray-200" aria-hidden="true">
                        <span class="text-emerald-400">$</span> docker compose up -d <span class="es-cursor" aria-hidden="true"></span>
                    </div>
                    <a href="{{ route('marketing.selfhost') }}" class="mt-auto inline-flex items-center gap-2 self-start font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300">
                        Explore selfhosting
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <!-- Build a business (forward card last) -->
                <div class="relative flex flex-col rounded-3xl border border-blue-300 bg-white p-8 ring-2 ring-blue-500/30 dark:border-blue-500/40 dark:bg-white/[0.06] dark:ring-blue-400/25" data-reveal>
                    <div class="mb-3 text-sm font-semibold text-blue-600 dark:text-blue-400">Build a business on it</div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">You are on the right page</h3>
                    <p class="mb-5 text-gray-500 dark:text-gray-400">Turn the same install into a white-label ticketing SaaS: multi-tenant subdomains, subscription billing, and plan gating included.</p>
                    <div class="mb-6 flex items-center gap-2" aria-hidden="true">
                        @foreach ([['from-blue-500 to-cyan-500', ''], ['from-sky-400 to-cyan-400', 'mt-1.5'], ['from-amber-400 to-orange-400', 'mt-3']] as $i => $glyph)
                            <span class="-ml-3 first:ml-0 h-8 w-14 skew-x-[-18deg] rounded-lg bg-gradient-to-br {{ $glyph[0] }} opacity-{{ 90 - $i * 15 }} {{ $glyph[1] }}"></span>
                        @endforeach
                        <span class="es-tag ml-2">the stack, yours</span>
                    </div>
                    <a href="{{ route('marketing.docs.saas.setup') }}" class="mt-auto inline-flex items-center justify-center gap-2 self-start rounded-xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white transition-colors hover:from-blue-500 hover:to-sky-500">
                        Read the SaaS Setup Guide
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
            <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>Related: <x-link href="{{ route('marketing.white_label') }}">White-label features</x-link> · <x-link href="{{ route('marketing.docs.saas.custom_domains') }}">Custom domains for customers</x-link></p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    @php
        $faqs = [
            [
                'q' => 'What is white-label ticketing software?',
                'a' => 'White-label ticketing software is an event ticketing platform you rebrand and sell as your own product. Event Schedule goes further than most: you selfhost the entire open source platform, so customers sign up on your domain and pay through your Stripe account. The only trace of us is a small attribution link on public schedule pages.',
            ],
            [
                'q' => 'How do I start an online ticketing business?',
                'a' => 'The technology half is now the easy half: deploy Event Schedule on a server, connect Stripe, set your prices, and open sign-ups. That gives you your own Eventbrite-style platform with subscriptions, ticketing, and check-in built in. The real work is picking a niche and finding your first customers, and you can put your energy there.',
            ],
            [
                'q' => 'How does the free white-label license work?',
                'a' => 'Event Schedule is open source under the Attribution Assurance License (AAL). You can run it commercially at no cost as long as you keep the attribution, which appears as a small link on public schedule pages. There are no license fees, no revenue share, and no per-ticket fees.',
            ],
            [
                'q' => 'Can I set my own subscription prices?',
                'a' => 'Yes. You create the products and prices in your own Stripe account, and the built-in Free, Pro, and Enterprise tiers use them. You also control the trial length with a single TRIAL_DAYS setting. Whatever your customers pay lands in your Stripe account.',
            ],
            [
                'q' => 'Is there a limit on customers or ticket sales?',
                'a' => 'No. The platform does not limit the number of customers, schedules, events, or tickets sold. Each customer gets their own subdomain on your domain, and capacity limits only exist where an event organizer chooses to set them.',
            ],
            [
                'q' => 'How is this different from a reseller or partner program?',
                'a' => 'Reseller programs rent you a brand skin on someone else\'s platform: typically a revenue share, per-ticket fees, and no code access. With Event Schedule you run the actual software on your own servers. Nobody can raise your rates, change your terms, or switch your platform off.',
            ],
            [
                'q' => 'What do I need to host it?',
                'a' => 'A server you control (a small VPS is enough to start), a domain with wildcard DNS for customer subdomains, and a Stripe account for billing. Install with Docker or the one-click Softaculous installer. The setup guide covers everything from environment variables to going live.',
            ],
            [
                'q' => 'Who handles GDPR and data protection?',
                'a' => 'You do, because the platform runs on your infrastructure and you are the data controller for your customers. That is a responsibility, but also an advantage: the data stays on servers you choose, and the open source code means you can audit exactly how it is handled.',
            ],
            [
                'q' => 'Can my customers use their own domain?',
                'a' => 'Yes. Every customer gets a subdomain like acme.yourdomain.com out of the box, and you can also point a customer\'s own domain at their schedule. The custom domains guide covers the DNS and proxy setup.',
                'more' => ['label' => 'Read the custom domains guide', 'href' => 'marketing.docs.saas.custom_domains'],
            ],
            [
                'q' => 'Do I get all features, or is there a paid tier for operators?',
                'a' => 'Selfhosted installations run with every Enterprise feature unlocked: ticketing with QR check-in, REST API and webhooks, newsletters, analytics, event graphics, and AI features with your own API keys. The Free, Pro, and Enterprise tiers exist for your customers, and you decide what each tier includes and costs.',
            ],
        ];
    @endphp
    <x-seo.faq-schema :items="$faqs" />
    <section id="faq" class="border-t border-gray-200 bg-gray-50 py-24 scroll-mt-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Common Questions
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-saas">questions</span></h2>
                <p class="mx-auto max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">What operators ask before they launch.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="pr-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">
                            <p>{{ $faq['a'] }}</p>
                            @isset($faq['more'])
                                <p class="mt-3"><x-link href="{{ route($faq['more']['href']) }}">{{ $faq['more']['label'] }}</x-link></p>
                            @endisset
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="start" class="relative bg-white px-2 py-16 scroll-mt-24 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-tenants absolute bottom-6 left-0 right-0 mx-auto flex h-12 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 16; $i++)
                            <span class="es-tenant" style="--tn-dur: {{ 2.6 + ($i % 5) * 0.4 }}s; --tn-delay: {{ ($i % 8) * 0.26 }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="mb-8 inline-flex items-center gap-3" aria-hidden="true">
                        <span class="es-tag !text-gray-400">clone</span>
                        <svg class="h-3.5 w-3.5 text-gray-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5" /></svg>
                        <span class="es-tag !text-gray-400">configure</span>
                        <svg class="h-3.5 w-3.5 text-gray-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5" /></svg>
                        <span class="es-tag !text-amber-400">charge</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to own <span class="text-gradient-saas">the whole stack?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        The setup guide takes you from a fresh server to live customer sign-ups. Stuck on anything? The GitHub discussions are open.
                    </p>

                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10">
                            Or use eventschedule.com instead
                        </a>
                        <a href="{{ route('marketing.docs.saas.setup') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Read the Setup Guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = [
            ['top', 'Top'],
            ['machinery', 'Already built'],
            ['revenue', 'The math'],
            ['compare', 'Build vs rent vs own'],
            ['catch', 'Why free'],
            ['launch', 'Launch plan'],
            ['product', 'Your product'],
            ['faq', 'FAQ'],
            ['start', 'Get started'],
        ];
    @endphp
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Revenue calculator: user-driven arithmetic only, no autonomous motion. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var calc = document.getElementById('es-calc');
            if (!calc) return;
            var customers = document.getElementById('es-r-customers');
            var price = document.getElementById('es-r-price');
            var outCustomers = document.getElementById('es-out-customers');
            var outPrice = document.getElementById('es-out-price');
            var outMonth = document.getElementById('es-out-month');
            var outYear = document.getElementById('es-out-year');
            var outCutLow = document.getElementById('es-out-cut-low');
            var outCutHigh = document.getElementById('es-out-cut-high');
            if (!customers || !price || !outMonth) return;
            var fmt = new Intl.NumberFormat('en-US');
            function fill(input) {
                var min = parseFloat(input.min) || 0;
                var max = parseFloat(input.max) || 100;
                var val = parseFloat(input.value) || 0;
                input.style.setProperty('--fill', (((val - min) / (max - min)) * 100).toFixed(1) + '%');
            }
            function update() {
                var c = parseInt(customers.value, 10) || 0;
                var p = parseInt(price.value, 10) || 0;
                var monthly = c * p;
                var yearly = monthly * 12;
                if (outCustomers) outCustomers.textContent = fmt.format(c);
                if (outPrice) outPrice.textContent = '$' + fmt.format(p);
                outMonth.textContent = '$' + fmt.format(monthly);
                if (outYear) outYear.textContent = '$' + fmt.format(yearly);
                if (outCutLow) outCutLow.textContent = '$' + fmt.format(Math.round(yearly * 0.1));
                if (outCutHigh) outCutHigh.textContent = '$' + fmt.format(Math.round(yearly * 0.3));
                fill(customers);
                fill(price);
            }
            customers.addEventListener('input', update);
            price.addEventListener('input', update);
            update();
        })();
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
