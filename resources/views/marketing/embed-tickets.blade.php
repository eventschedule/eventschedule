<x-marketing-layout>
    <x-slot name="title">Embed Tickets on Any Website - Event Schedule</x-slot>
    <x-slot name="description">Embed a ticket purchase or RSVP form on any website with one line of code. Supports all payment methods, dark mode, and 11 languages.</x-slot>
    <x-slot name="breadcrumbTitle">Embed Tickets</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Embed Tickets",
        "description": "Embed a ticket purchase or RSVP form on any website with one line of code. Supports all payment methods, dark mode, and 11 languages.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Embeddable Ticket Widget"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do I embed a ticket form on my website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to your event in the admin portal, click the Embed Tickets button, and copy the iframe code. Paste it into your website's HTML wherever you want the ticket form to appear."
                }
            },
            {
                "@type": "Question",
                "name": "Does it support all payment methods?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The embedded ticket widget supports Stripe, Invoice Ninja, custom payment URLs, and cash or at-the-door payments. Free and cash checkouts complete entirely inside the embed. Stripe, Invoice Ninja, and custom payment URL checkouts redirect to the payment provider in the parent window and return after payment."
                }
            },
            {
                "@type": "Question",
                "name": "Can I use the embed for free events and RSVPs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Absolutely. The embed works for paid tickets, free registrations, and RSVP-only events. If your event has no ticket price, the widget acts as a simple registration form."
                }
            },
            {
                "@type": "Question",
                "name": "Does the widget support dark mode?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The ticket embed automatically detects your visitor's system preference and renders in light or dark mode. You can also force a specific mode with a URL parameter."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Embed Tickets",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Website Integration Software",
        "operatingSystem": "Web",
        "description": "Embed a ticket purchase or RSVP form on any website with one line of code. Supports all payment methods, dark mode, and 11 languages.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included with Pro plan"
        },
        "featureList": [
            "One-line iframe embed",
            "Stripe, Invoice Ninja, custom payment URL, and cash payment support",
            "Promo codes",
            "Custom fields",
            "Waitlist support",
            "Dark mode support",
            "RTL language support",
            "11 language support",
            "Mobile responsive"
        ],
        "url": "{{ url()->current() }}",
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
        /* For embed-tickets "The Widget" styles. The shared es-* motion system lives in
           marketing.css; this holds the blue glow gradient, the drifting ticket-widget
           card, and the ticket-stub motif (embedded ticket forms across the web). */
        .text-gradient-embtix {
            background: linear-gradient(135deg, #2563eb, #3b82f6, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-embtix {
            background: linear-gradient(135deg, #60a5fa, #3b82f6, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-embtix-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-embtix-float { animation: es-embtix-float 6s ease-in-out infinite; }

        /* Ticket-stub motif: ticket stubs with a perforation blink in a wave, like
           embedded ticket forms placed across the web. */
        .es-stubs { display: flex; align-items: center; }
        .es-stub {
            position: relative; flex: 0 0 auto; width: 24px; height: 13px; border-radius: 3px;
            background: rgba(59, 130, 246, 0.18);
            animation: es-stub-pulse var(--st-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--st-delay, 0s);
        }
        .es-stub::before {
            content: ""; position: absolute; left: 7px; top: -3px; bottom: -3px; width: 0;
            border-left: 1.5px dashed rgba(59, 130, 246, 0.45);
        }
        @keyframes es-stub-pulse {
            0%, 100% { background: rgba(59, 130, 246, 0.14); box-shadow: none; }
            50% { background: rgba(59, 130, 246, 0.7); box-shadow: 0 0 8px rgba(59, 130, 246, 0.4); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-embtix-float, .es-stub, .animate-pulse-slow { animation: none !important; }
            .es-stub { background: rgba(59, 130, 246, 0.35); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: embed tickets                                       -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.28), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.14), rgba(59, 130, 246, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Ticket-stub motif along the bottom edge -->
            <div class="es-stubs absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-4 px-8 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 26; $i++)
                    @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.2; @endphp
                    <span class="es-stub" style="--st-dur: {{ $dur }}s; --st-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Embed Tickets</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Sell tickets from</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-embtix">any website</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Embed a ticket purchase or registration form on your website with a single line of code.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.sharing') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Sharing guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>A full checkout, <span class="text-gradient-embtix">on any page</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Copy and paste (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    One Line
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Copy and paste</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">One iframe tag is all you need. Copy the embed code from your event settings and paste it anywhere on your website.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No JavaScript</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No dependencies</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-80" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Embed code</div>
                                    <div class="break-all rounded-lg bg-gray-200 p-3 font-mono text-xs leading-relaxed text-blue-600 dark:bg-white/5 dark:text-blue-300">
                                        &lt;iframe src="https://demo.eventschedule.com/concert/a1b2c3?tickets=true&amp;embed=true" width="100%" height="700" frameborder="0" style="border: none;"&gt;&lt;/iframe&gt;
                                    </div>
                                    <div class="mt-3 flex items-center gap-2 text-xs text-blue-500 dark:text-blue-400">
                                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Copy to clipboard
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Payments
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">All payment methods</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Buyers select tickets and enter details inside the embed. Supports all payment methods.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @php
                                $payMethods = [
                                    ['Stripe', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                                    ['Invoice Ninja', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                    ['Cash or at the door', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                                    ['Custom payment URL', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                                ];
                            @endphp
                            @foreach ($payMethods as $pi => $pm)
                                <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-2.5 dark:border-white/10 dark:bg-white/10" style="--i: {{ $pi }};">
                                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-blue-500/20">
                                        <svg aria-hidden="true" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pm[1] }}" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $pm[0] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- All features included -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Full Features
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">All features included</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">The embed is a full ticket checkout, not a cut-down version.</p>
                        <div class="mt-auto space-y-2.5">
                            @foreach (['Promo codes', 'Custom fields', 'Waitlist support', 'Multiple ticket types', 'Confirmation emails'] as $feat)
                                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <svg aria-hidden="true" class="h-4 w-4 flex-shrink-0 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $feat }}
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Dark mode + RTL (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    Dark Mode + RTL
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Adapts to any context</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">The widget automatically matches your visitor's system preference for light or dark mode, and supports right-to-left languages including Arabic and Hebrew. You can also force dark mode with <code class="rounded bg-blue-500/15 px-1.5 py-0.5 text-sm text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">?dark=true</code> or set a language with <code class="rounded bg-blue-500/15 px-1.5 py-0.5 text-sm text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">?lang=xx</code> in the embed URL.</p>
                            </div>
                            <div class="flex gap-4" aria-hidden="true">
                                <div class="flex-1 overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                                    <div class="border-b border-gray-200 bg-gray-100 p-3">
                                        <div class="mb-2 text-[10px] text-gray-400">Light mode</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2.5 w-full rounded bg-gray-200"></div>
                                            <div class="h-2.5 w-3/4 rounded bg-blue-200"></div>
                                            <div class="mt-2 h-5 w-full rounded bg-blue-400"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1 overflow-hidden rounded-2xl border border-white/10">
                                    <div class="border-b border-[#2d2d30] bg-[#1e1e1e] p-3">
                                        <div class="mb-2 text-[10px] text-gray-500">Dark mode</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2.5 w-full rounded bg-[#2d2d30]"></div>
                                            <div class="h-2.5 w-3/4 rounded bg-blue-900"></div>
                                            <div class="mt-2 h-5 w-full rounded bg-blue-600"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Mobile responsive (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Responsive
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Mobile ready</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">The ticket form looks great and works flawlessly on phones, tablets, and desktops alike.</p>
                        <div class="mt-auto flex items-end justify-center gap-3" aria-hidden="true">
                            <div class="w-10 overflow-hidden rounded-xl border border-gray-300 bg-gray-200 dark:border-white/20 dark:bg-white/10" style="height: 64px;">
                                <div class="h-full space-y-1 p-1.5">
                                    <div class="h-1.5 w-full rounded bg-blue-300 dark:bg-blue-500/40"></div>
                                    <div class="h-1.5 w-full rounded bg-gray-300 dark:bg-white/10"></div>
                                    <div class="mt-1 h-3 w-full rounded bg-blue-400 dark:bg-blue-600"></div>
                                </div>
                            </div>
                            <div class="w-16 overflow-hidden rounded-xl border border-gray-300 bg-gray-200 dark:border-white/20 dark:bg-white/10" style="height: 80px;">
                                <div class="h-full space-y-1.5 p-2">
                                    <div class="h-2 w-full rounded bg-blue-300 dark:bg-blue-500/40"></div>
                                    <div class="h-2 w-3/4 rounded bg-gray-300 dark:bg-white/10"></div>
                                    <div class="mt-1 h-4 w-full rounded bg-blue-400 dark:bg-blue-600"></div>
                                </div>
                            </div>
                            <div class="w-24 overflow-hidden rounded-xl border border-gray-300 bg-gray-200 dark:border-white/20 dark:bg-white/10" style="height: 96px;">
                                <div class="h-full space-y-2 p-2.5">
                                    <div class="h-2 w-full rounded bg-blue-300 dark:bg-blue-500/40"></div>
                                    <div class="h-2 w-5/6 rounded bg-gray-300 dark:bg-white/10"></div>
                                    <div class="mt-1 h-5 w-full rounded bg-blue-400 dark:bg-blue-600"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Pro plan and above -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Availability
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Pro plan and above</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Ticket embeds require a paid plan on the hosted platform. Selfhosted users get this feature included at no extra cost.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-sm text-blue-700 dark:text-blue-300">Pro plan</span>
                            <span class="inline-flex items-center rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-sm text-blue-700 dark:text-blue-300">Free for selfhosted</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. How it works (dark band)                                 -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-stubs absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-4 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 24; $i++)
                        @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.2; @endphp
                        <span class="es-stub" style="--st-dur: {{ $dur }}s; --st-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>How it <span class="text-gradient-embtix">works</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Up and running in three steps.</p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                    @php
                        $steps = [
                            ['Enable tickets or RSVP', 'Add at least one ticket type or enable RSVP on your event from the admin portal.'],
                            ['Copy the embed code', 'Edit your event, scroll to the Tickets section, and click the "Embed Tickets" (or "Embed Registration" for RSVP-only events) link to copy the iframe code.'],
                            ['Paste into your website', 'Drop the iframe tag into your website\'s HTML. The ticket form appears instantly, ready to take purchases.'],
                        ];
                    @endphp
                    @foreach ($steps as $si => $step)
                        <div data-reveal class="text-center">
                            <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">{{ $si + 1 }}</div>
                            <h3 class="mb-3 text-xl font-bold text-white">{{ $step[0] }}</h3>
                            <p class="text-gray-300">{{ $step[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Guide & next feature                                     -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-blue-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-sky-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.tickets') }}#embed-widget" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Read the guide</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to set up and customize the ticket embed widget.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                        Read guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Ticketing feature -->
                <a href="{{ marketing_url('/features/ticketing') }}" data-reveal class="group flex flex-col rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-blue-900 dark:to-sky-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">Ticketing</h3>
                    <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Sell tickets, manage attendees, and track sales for all your events.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-libraries') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Libraries</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-community-centers') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Community Centers</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-embtix">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about embedding your ticket form.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I embed a ticket form on my website?', 'Go to your event in the admin portal, click the Embed Tickets button, and copy the iframe code. Paste it into your website\'s HTML wherever you want the ticket form to appear.'],
                    ['Does it support all payment methods?', 'Yes. The embedded ticket widget supports Stripe, Invoice Ninja, custom payment URLs, and cash or at-the-door payments. Free and cash checkouts complete entirely inside the embed. Stripe, Invoice Ninja, and custom payment URL checkouts redirect to the payment provider in the parent window and return after payment.'],
                    ['Can I use the embed for free events and RSVPs?', 'Absolutely. The embed works for paid tickets, free registrations, and RSVP-only events. If your event has no ticket price, the widget acts as a simple registration form.'],
                    ['Does the widget support dark mode?', 'Yes. The ticket embed automatically detects your visitor\'s system preference and renders in light or dark mode. You can also force a specific mode with a URL parameter.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <!-- 6. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Add your full event calendar to any website with a single iframe"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Collect dietary preferences, t-shirt sizes, or any info you need from ticket buyers"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets, manage attendees, and track sales for your events"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-stubs absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-4 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 20; $i++)
                            @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.2; @endphp
                            <span class="es-stub" style="--st-dur: {{ $dur }}s; --st-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start selling <span class="text-gradient-embtix">tickets today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Add a ticket purchase form to any website in seconds. No coding required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No coding required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
