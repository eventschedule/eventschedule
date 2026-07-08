<x-marketing-layout>
    <x-slot name="title">Event Graphics | Auto-Generated Shareable Images - Event Schedule</x-slot>
    <x-slot name="description">Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.</x-slot>
    <x-slot name="breadcrumbTitle">Event Graphics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Event Graphics",
        "description": "Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Graphics Generation"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What formats are available for event graphics?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule generates shareable images with your event details overlaid on a customizable header, plus formatted text ready to paste into Instagram, WhatsApp, email, and other platforms."
                }
            },
            {
                "@type": "Question",
                "name": "Can I customize the header image?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Upload a custom header image for your schedule and it will be used as the background for all generated event graphics. If no header is set, a default branded graphic is used."
                }
            },
            {
                "@type": "Question",
                "name": "Is the event graphics feature free?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event graphics generation is available on the Pro plan. The Pro plan costs $5/month with a 7-day free trial."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Event Graphics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Graphics Generator",
        "operatingSystem": "Web",
        "description": "Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.",
        "offers": {
            "@type": "Offer",
            "price": "5",
            "priceCurrency": "USD",
            "description": "Pro plan with 7-day free trial"
        },
        "featureList": [
            "Auto-generated event images",
            "Custom header images",
            "Formatted text output",
            "Social media ready",
            "Schedule overview graphic",
            "One-click download"
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
        /* For event-graphics "The Gallery" styles. The shared es-* motion system lives in
           marketing.css; this holds the orange glow gradient, the drifting graphic preview,
           and the image-tile motif (generated graphics in every format). */
        .text-gradient-graphics {
            background: linear-gradient(135deg, #ea580c, #f97316, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(234, 88, 12, 0.3);
        }
        .dark .text-gradient-graphics {
            background: linear-gradient(135deg, #fb923c, #fbbf24, #fcd34d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 146, 60, 0.3);
        }
        @keyframes es-graphics-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-graphics-float { animation: es-graphics-float 6s ease-in-out infinite; }

        /* Image-tile motif: poster tiles in varied aspect ratios shimmer, like generated
           event graphics formatted for every platform. */
        .es-tiles { display: flex; align-items: center; }
        .es-tile {
            flex: 0 0 auto; border-radius: 5px;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.85), rgba(245, 158, 11, 0.85));
            animation: es-tile-shimmer var(--tl-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--tl-delay, 0s);
        }
        @keyframes es-tile-shimmer {
            0%, 100% { opacity: 0.3; transform: scale(0.88); }
            50% { opacity: 1; transform: scale(1); box-shadow: 0 0 10px rgba(249, 115, 22, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-graphics-float, .es-tile, .animate-pulse-slow { animation: none !important; }
            .es-tile { opacity: 0.6; transform: scale(0.95); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: event graphics                                      -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(249, 115, 22, 0.14), rgba(249, 115, 22, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Image-tile motif along the bottom edge -->
            <div class="es-tiles absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 20; $i++)
                    @php $dims = [[16, 24], [22, 22], [30, 18], [18, 26], [26, 20]][$i % 5]; $dur = 2.6 + ($i % 5) * 0.32; $delay = ($i % 11) * 0.22; @endphp
                    <span class="es-tile" style="width: {{ $dims[0] }}px; height: {{ $dims[1] }}px; --tl-dur: {{ $dur }}s; --tl-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Event Graphics</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Share your events</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-graphics">everywhere</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Auto-generate shareable images and formatted text for your upcoming events. Ready for Instagram, WhatsApp, email, and more.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-orange-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.event_graphics') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Event Graphics guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Beautiful graphics, <span class="text-gradient-graphics">zero design work</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Image graphics (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Image Graphics
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">One-click shareable images</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Generate beautiful event graphics automatically. Your event details are overlaid on your schedule's header image, ready to download and share.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-generated</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Custom header</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Download &amp; share</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" aria-hidden="true">
                                <div class="w-56 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="bg-gradient-to-br from-orange-400 to-amber-500 p-4 pb-6">
                                        <div class="mb-3 flex items-center gap-2">
                                            <div class="h-6 w-6 rounded-full bg-white/30"></div>
                                            <div class="h-2 w-20 rounded bg-white/40"></div>
                                        </div>
                                        <div class="mb-1 text-sm font-bold text-white">Summer Jazz Night</div>
                                        <div class="text-xs text-white/80">Saturday, Jul 12 at 8 PM</div>
                                        <div class="text-xs text-white/60">Blue Note Jazz Club</div>
                                    </div>
                                    <div class="flex items-center justify-center gap-3 p-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-500/20">
                                            <svg aria-hidden="true" class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        </div>
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-500/20">
                                            <svg aria-hidden="true" class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Formatted text -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Formatted Text
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Copy-paste ready</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Get pre-formatted event text to paste into any messaging app or social post.</p>
                        <div class="mt-auto space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-4 font-mono text-xs text-gray-600 dark:border-white/10 dark:bg-[#0f0f14] dark:text-gray-300" aria-hidden="true">
                            <div class="font-bold text-gray-900 dark:text-white">Summer Jazz Night</div>
                            <div>Saturday, Jul 12 at 8 PM</div>
                            <div>Blue Note Jazz Club</div>
                            <div class="text-orange-600 dark:text-orange-400">bluenote.eventschedule.com/...</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple platforms -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Share Anywhere
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Every platform</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Graphics optimized for every major sharing destination.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-orange-500/20 bg-orange-500/10 p-3" style="--i: 0;">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Instagram</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">WhatsApp</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Email</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 3;">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Facebook</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Custom header (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                    Customizable
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Your brand, your graphics</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Upload a custom header image for your schedule and every generated graphic uses it as the background. Your events, your look.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs font-medium text-gray-500 dark:text-gray-400">Header Image</div>
                                <div class="mb-4 flex h-24 w-full items-center justify-center rounded-xl bg-gradient-to-r from-orange-400 to-amber-500">
                                    <svg aria-hidden="true" class="h-8 w-8 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="h-2 flex-1 rounded bg-orange-200 dark:bg-orange-500/20"></div>
                                    <div class="rounded bg-orange-500 px-3 py-1 text-xs font-medium text-white">Upload</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Schedule graphic -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Schedule View
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Full schedule graphic</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Generate a graphic showing all upcoming events at once. Perfect for posting your weekly or monthly lineup.</p>
                        <div class="mt-auto space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            @php
                                $lineup = [['Jazz Night', 'Sat 8 PM', 'bg-orange-500', true], ['Open Mic', 'Sun 7 PM', 'bg-amber-500', false], ['Trivia Night', 'Tue 8 PM', 'bg-yellow-500', false]];
                            @endphp
                            @foreach ($lineup as $li => [$name, $when, $bar, $active])
                                <div class="es-ai-field flex items-center gap-2 rounded-lg p-2 {{ $active ? 'border border-orange-500/20 bg-orange-500/10' : 'bg-gray-200 dark:bg-white/5' }}" style="--i: {{ $li }};">
                                    <div class="h-6 w-1 rounded-full {{ $bar }}"></div>
                                    <div>
                                        <div class="text-xs font-medium {{ $active ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">{{ $name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $when }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- No design skills (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Zero Effort
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">No design skills needed</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Graphics are generated automatically from your event details. Just create an event and the graphic is ready to share. No templates, no editors, no fuss.</p>
                            </div>
                            <div class="flex-shrink-0" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-28 rounded-xl border border-orange-200 bg-orange-100 p-4 dark:border-orange-400/30 dark:bg-orange-500/20">
                                        <div class="mb-2 text-center text-xs text-orange-600 dark:text-orange-300">Event</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-orange-400/40"></div>
                                            <div class="h-2 w-3/4 rounded bg-orange-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-orange-400/40"></div>
                                        </div>
                                    </div>
                                    <svg aria-hidden="true" class="h-6 w-6 shrink-0 text-orange-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    <div class="w-28 rounded-xl border border-amber-200 bg-amber-100 p-4 dark:border-amber-400/30 dark:bg-amber-500/20">
                                        <div class="mb-2 text-center text-xs text-amber-600 dark:text-amber-300">Graphic</div>
                                        <div class="h-12 w-full rounded bg-gradient-to-br from-orange-400 to-amber-500"></div>
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
    <!-- 3. How it works (dark band)                                 -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(234, 88, 12, 0.24), rgba(234, 88, 12, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-tiles absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 18; $i++)
                        @php $dims = [[16, 24], [22, 22], [30, 18], [18, 26], [26, 20]][$i % 5]; $dur = 2.6 + ($i % 5) * 0.32; $delay = ($i % 11) * 0.22; @endphp
                        <span class="es-tile" style="width: {{ $dims[0] }}px; height: {{ $dims[1] }}px; --tl-dur: {{ $dur }}s; --tl-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Share events in <span class="text-gradient-graphics">three steps</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Beautiful graphics, zero design work.</p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                    @php
                        $steps = [
                            ['Create your event', 'Add event details to your schedule as usual.'],
                            ['Generate graphics', 'Open the event graphic view to auto-generate shareable images and text.'],
                            ['Share anywhere', 'Download the image or copy the formatted text for Instagram, WhatsApp, email, and more.'],
                        ];
                    @endphp
                    @foreach ($steps as $si => $step)
                        <div data-reveal class="text-center">
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-2xl font-bold text-white shadow-lg shadow-orange-500/25">{{ $si + 1 }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $step[0] }}</h3>
                            <p class="text-sm text-gray-300">{{ $step[1] }}</p>
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
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-orange-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-amber-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.getting_started') }}" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-orange-500/20 bg-orange-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-orange-600 dark:text-white dark:group-hover:text-orange-400">Read the guide</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to get the most out of event graphics.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-orange-500 transition-all group-hover:gap-3 dark:text-orange-400">
                        Read guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/newsletters') }}" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Newsletters</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Send branded newsletters to followers and keep your audience engaged.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-500 transition-all group-hover:gap-3 dark:text-sky-400">
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
                        <a href="{{ marketing_url('/for-musicians') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Musicians</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-bars') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Bars &amp; Pubs</span>
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-graphics">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about event graphics.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What formats are available for event graphics?', 'Event Schedule generates shareable images with your event details overlaid on a customizable header, plus formatted text ready to paste into Instagram, WhatsApp, email, and other platforms.'],
                    ['Can I customize the header image?', 'Yes. Upload a custom header image for your schedule and it will be used as the background for all generated event graphics. If no header is set, a default branded graphic is used.'],
                    ['Is the event graphics feature free?', 'Event graphics generation is available on the Pro plan. The Pro plan costs $5/month with a 7-day free trial.'],
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
                        name="AI Features"
                        description="Import, generate content, create brand style, and more with AI"
                        :url="marketing_url('/features/ai')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send branded newsletters to followers and ticket buyers"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-orange-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-tiles absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 14; $i++)
                            @php $dims = [[16, 24], [22, 22], [30, 18], [18, 26], [26, 20]][$i % 5]; $dur = 2.6 + ($i % 5) * 0.32; $delay = ($i % 11) * 0.22; @endphp
                            <span class="es-tile" style="width: {{ $dims[0] }}px; height: {{ $dims[1] }}px; --tl-dur: {{ $dur }}s; --tl-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start sharing <span class="text-gradient-graphics">your events</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Generate beautiful event graphics in seconds. No design skills required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-orange-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No design skills required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
