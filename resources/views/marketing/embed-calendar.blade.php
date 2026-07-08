<x-marketing-layout>
    <x-slot name="title">Embed Calendar | Add Events to Any Website - Event Schedule</x-slot>
    <x-slot name="description">Embed your event calendar on any website with one line of code. Responsive iframe with dark mode support and 11 languages.</x-slot>
    <x-slot name="breadcrumbTitle">Embed Calendar</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Embed Calendar",
        "description": "Embed your event calendar on any website with one line of code. Responsive iframe with dark mode support and 11 languages.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Embeddable Calendar Widget"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do I embed my schedule on my website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Copy the embed code from your schedule settings and paste it into your website's HTML. The iframe embed automatically adapts to your site and is fully responsive."
                }
            },
            {
                "@type": "Question",
                "name": "Can I customize the embedded calendar's appearance?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can control the colors, layout, and which information is displayed. The embed automatically adapts to light and dark mode and is fully responsive on mobile."
                }
            },
            {
                "@type": "Question",
                "name": "Does the embed slow down my website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. The embed loads asynchronously and is optimized for performance. It won't block your page from rendering and adds minimal weight to your site."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Embed Calendar",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Website Integration Software",
        "operatingSystem": "Web",
        "description": "Embed your event calendar on any website with one line of code. Responsive iframe with dark mode and multilingual support.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "One-line iframe embed",
            "Responsive design",
            "Dark mode support",
            "11 language support",
            "URL parameter customization",
            "Simplified calendar view"
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
        /* For embed-calendar "The Paste" styles. The shared es-* motion system lives in
           marketing.css; this holds the blue glow gradient, the drifting embed-code card,
           and the code-bracket motif (embed snippets blinking across the page). */
        .text-gradient-embed {
            background: linear-gradient(135deg, #2563eb, #3b82f6, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-embed {
            background: linear-gradient(135deg, #60a5fa, #3b82f6, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-embed-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-embed-float { animation: es-embed-float 6s ease-in-out infinite; }

        /* Code-bracket motif: angle brackets blink in a wave, like embed snippets
           being pasted across the web. */
        .es-brackets { display: flex; align-items: center; }
        .es-bracket {
            flex: 0 0 auto; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 800; line-height: 1; color: rgba(59, 130, 246, 0.85);
            animation: es-bracket-blink var(--bk-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--bk-delay, 0s);
        }
        @keyframes es-bracket-blink {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-embed-float, .es-bracket, .animate-pulse-slow { animation: none !important; }
            .es-bracket { opacity: 0.55; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: embed calendar                                      -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.28), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.14), rgba(59, 130, 246, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Code-bracket motif along the bottom edge -->
            <div class="es-brackets absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-45 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 16; $i++)
                    @php $g = ['&lt;/&gt;', '&lt;&gt;', '/&gt;', '&lt;/&gt;'][$i % 4]; $sz = [18, 26, 20, 30][$i % 4]; $dur = 2.4 + ($i % 4) * 0.35; $delay = ($i % 8) * 0.28; @endphp
                    <span class="es-bracket" style="font-size: {{ $sz }}px; --bk-dur: {{ $dur }}s; --bk-delay: {{ $delay }}s;">{!! $g !!}</span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Embed Calendar</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your calendar,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-embed">everywhere</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Add your event calendar to any website with a single line of code.
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>One line of code, <span class="text-gradient-embed">anywhere you want</span></h2>
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
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Just one iframe tag is all you need. Copy the code from your schedule settings and paste it into your website.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No JavaScript</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No dependencies</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-80" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Embed code</div>
                                    <div class="break-all rounded-lg bg-gray-200 p-3 font-mono text-xs leading-relaxed text-blue-600 dark:bg-white/5 dark:text-blue-300">
                                        &lt;iframe src="https://your-schedule.eventschedule.com?embed=true" width="100%" height="800"&gt;&lt;/iframe&gt;
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

                <!-- Preview first -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Preview first</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See exactly what the embed will look like before adding it to your site.</p>
                        <div class="mt-auto overflow-hidden rounded-xl border border-gray-300 bg-gray-200 dark:border-white/20 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-100 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                <div class="h-2 w-2 rounded-full bg-red-500/70"></div>
                                <div class="h-2 w-2 rounded-full bg-yellow-500/70"></div>
                                <div class="h-2 w-2 rounded-full bg-green-500/70"></div>
                            </div>
                            <div class="space-y-1.5 p-3">
                                <div class="h-3 w-full rounded bg-blue-200 dark:bg-blue-500/20"></div>
                                <div class="h-3 w-3/4 rounded bg-gray-200 dark:bg-white/5"></div>
                                <div class="h-3 w-5/6 rounded bg-blue-200 dark:bg-blue-500/20"></div>
                                <div class="h-3 w-2/3 rounded bg-gray-200 dark:bg-white/5"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Just the calendar -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                            </svg>
                            Clean Layout
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Just the calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">The embed shows only your events, without headers, footers, or navigation. Clean and focused.</p>
                        <div class="mt-auto flex gap-3" aria-hidden="true">
                            <div class="flex-1 rounded-lg border border-gray-200 bg-gray-100 p-2 text-center dark:border-white/10 dark:bg-white/10">
                                <div class="mb-1 text-[10px] text-gray-400 dark:text-gray-500">Full site</div>
                                <div class="space-y-1">
                                    <div class="h-1 w-full rounded bg-gray-400 dark:bg-gray-500"></div>
                                    <div class="h-4 rounded bg-gray-300 dark:bg-white/10"></div>
                                    <div class="h-1 w-full rounded bg-gray-400 dark:bg-gray-500"></div>
                                </div>
                            </div>
                            <div class="flex-1 rounded-lg border border-blue-200 bg-blue-100 p-2 text-center dark:border-blue-500/30 dark:bg-blue-500/20">
                                <div class="mb-1 text-[10px] text-blue-600 dark:text-blue-300">Embed</div>
                                <div class="h-6 rounded bg-blue-200 dark:bg-blue-500/20"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Customize via URL (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    URL Parameters
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Customize via URL</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Control the language, filter by sub-schedule, and more using simple query parameters in the embed URL.</p>
                            </div>
                            <div class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="es-ai-field rounded-xl border border-blue-200 bg-blue-100 p-3 dark:border-blue-500/30 dark:bg-blue-500/20" style="--i: 0;">
                                    <code class="font-mono text-xs text-blue-600 dark:text-blue-300">?lang=es</code>
                                    <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Display in Spanish</div>
                                </div>
                                <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                    <code class="font-mono text-xs text-gray-600 dark:text-gray-300">?schedule=jazz</code>
                                    <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Filter by sub-schedule</div>
                                </div>
                                <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                    <code class="font-mono text-xs text-gray-600 dark:text-gray-300">?dark=true</code>
                                    <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Force dark mode</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Free for everyone (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Free
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Free for everyone</h3>
                                <p class="mb-4 text-gray-500 dark:text-gray-400 lg:text-lg">Embedding your calendar is free on all plans, including selfhosted. No upgrade required.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-sm text-blue-700 dark:text-blue-300">Free on all plans</span>
                                    <span class="inline-flex items-center rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-sm text-blue-700 dark:text-blue-300">Free for selfhosted</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" aria-hidden="true">
                                <div class="w-[200px] rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-sky-500">
                                            <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">All Plans</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
                                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Embed enabled
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Built-in feedback -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Feedback
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Built-in feedback</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Automatically collect post-event feedback from attendees to improve your future events.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">How was the event?</div>
                            <div class="mb-3 flex gap-1">
                                @for ($s = 0; $s < 4; $s++)
                                    <svg aria-hidden="true" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                                <svg aria-hidden="true" class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div class="mb-1.5 h-2 w-full rounded bg-gray-200 dark:bg-white/10"></div>
                            <div class="h-2 w-2/3 rounded bg-gray-200 dark:bg-white/10"></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Live demo (dark band)                                    -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mb-12 text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>See it <span class="text-gradient-embed">in action</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">A live embed of a demo schedule, powered by a single iframe tag.</p>
                </div>

                <!-- Browser frame mockup wrapping the real iframe -->
                <div data-reveal class="overflow-hidden rounded-2xl border border-white/10 bg-[#0f0f14] shadow-2xl shadow-blue-500/20">
                    <!-- Browser chrome bar with dots + fake URL -->
                    <div class="flex items-center gap-3 border-b border-white/10 bg-white/5 px-4 py-3">
                        <div class="flex gap-1.5">
                            <div class="h-3 w-3 rounded-full bg-red-500/70"></div>
                            <div class="h-3 w-3 rounded-full bg-yellow-500/70"></div>
                            <div class="h-3 w-3 rounded-full bg-green-500/70"></div>
                        </div>
                        <div class="flex-1">
                            <div class="truncate rounded-lg bg-white/10 px-3 py-1.5 font-mono text-xs text-gray-400">
                                your-website.com/events
                            </div>
                        </div>
                    </div>
                    <!-- Actual live iframe -->
                    <iframe src="{{ route('role.view_guest', ['subdomain' => 'simpsons']) }}?embed=true"
                            width="100%" height="800" loading="lazy"
                            title="Live demo of an embedded Event Schedule calendar"
                            style="border: none; display: block; background: #ffffff;"></iframe>
                </div>

                <!-- Caption -->
                <p class="mt-6 text-center text-sm text-gray-400">
                    <a href="{{ route('role.view_guest', ['subdomain' => 'simpsons']) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1 transition-colors hover:text-blue-400">
                        View full schedule
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. How it works                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-embed">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Up and running in three steps.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @php
                    $steps = [
                        ['Copy the embed code', 'Go to your schedule in the admin portal, open the menu, and click "Embed Schedule" to copy the iframe code.'],
                        ['Paste into your website', 'Add the iframe tag to your website\'s HTML wherever you want the calendar to appear.'],
                        ['Customize with URL parameters', 'Optionally set the language, filter by sub-schedule, or force dark mode using query parameters.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">{{ $si + 1 }}</div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $step[0] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Guide & next feature                                     -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-blue-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-sky-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.sharing') }}" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Read the guide</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to get the most out of embedding your calendar.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                        Read guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/custom-fields') }}" data-reveal class="group flex flex-col rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-100 to-orange-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-amber-900 dark:to-orange-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-300">Custom Fields</h3>
                    <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Collect dietary preferences, t-shirt sizes, or any info you need from ticket buyers.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-amber-500 transition-all group-hover:gap-3 dark:text-amber-400">
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
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-embed">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about embedding your calendar.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I embed my schedule on my website?', 'Copy the embed code from your schedule settings and paste it into your website\'s HTML. The iframe embed automatically adapts to your site and is fully responsive.'],
                    ['Can I customize the embedded calendar\'s appearance?', 'Yes. You can control the colors, layout, and which information is displayed. The embed automatically adapts to light and dark mode and is fully responsive on mobile.'],
                    ['Does the embed slow down my website?', 'No. The embed loads asynchronously and is optimized for performance. It won\'t block your page from rendering and adds minimal weight to your site.'],
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
    <!-- 7. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Collect additional info from attendees with custom form fields"
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
                        name="Sub-schedules"
                        description="Organize events into categories within your schedule"
                        :url="marketing_url('/features/sub-schedules')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Tickets"
                        description="Embed a ticket purchase or RSVP form on any website with one line of code"
                        :url="marketing_url('/features/embed-tickets')"
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
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-brackets absolute bottom-8 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 12; $i++)
                            @php $g = ['&lt;/&gt;', '&lt;&gt;', '/&gt;', '&lt;/&gt;'][$i % 4]; $sz = [18, 26, 20, 30][$i % 4]; $dur = 2.4 + ($i % 4) * 0.35; $delay = ($i % 8) * 0.28; @endphp
                            <span class="es-bracket" style="font-size: {{ $sz }}px; --bk-dur: {{ $dur }}s; --bk-delay: {{ $delay }}s;">{!! $g !!}</span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Embed your <span class="text-gradient-embed">calendar today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Add your event schedule to any website in seconds. No coding required.
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
