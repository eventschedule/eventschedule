<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.ai_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.ai_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">AI Features</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule AI Features",
        "description": "AI-powered event management. Generate flyers, write descriptions, create brand style, parse text and images, scan agendas, translate to 11 languages, create events via WhatsApp, and automate with a full API for AI agents.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "AI-Powered Event Management"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What formats does the AI parser support?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI can parse plain text, images (JPEG, PNG, GIF, WebP), screenshots, flyers, agendas, and setlists. It handles both single events and multi-event documents, extracting each event individually."
                }
            },
            {
                "@type": "Question",
                "name": "Which AI features are free vs. Enterprise?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "AI event parsing and instant translation are available on all plans including the free tier. Style generation, content generation, flyer generation, graphic text processing, and WhatsApp event creation are Enterprise features."
                }
            },
            {
                "@type": "Question",
                "name": "How accurate is the AI?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI is highly accurate for standard event formats. You always get to review and edit the results before saving. You can also add custom prompts to guide the AI for your specific format."
                }
            },
            {
                "@type": "Question",
                "name": "What can AI Style Generation create?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "AI Style Generation creates a complete visual identity for your schedule including profile image, header image, background image, accent color, and font. You can add custom style instructions and regenerate individual elements."
                }
            },
            {
                "@type": "Question",
                "name": "How does WhatsApp event creation work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Send a text message or photo of a flyer to your schedule's WhatsApp number. AI parses the details, auto-generates a flyer, and adds the event to your schedule automatically."
                }
            },
            {
                "@type": "Question",
                "name": "What languages are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI can parse events in any language and extract details correctly. The translation feature supports 11 languages including English, Spanish, French, German, Italian, Portuguese, Dutch, Hebrew, Arabic, Estonian, and Russian."
                }
            },
            {
                "@type": "Question",
                "name": "Can AI agents use Event Schedule programmatically?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule provides a full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. AI agents can create, update, and manage events programmatically with smart creation features and webhook notifications."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule AI Features",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "AI Event Management Software",
        "operatingSystem": "Web",
        "description": "AI-powered event management. Generate flyers, write descriptions, create brand style, parse text and images, scan agendas, translate to 11 languages, create events via WhatsApp, and automate with a full API for AI agents.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "AI-powered text parsing",
            "Image and flyer import",
            "Agenda and setlist parsing",
            "Multi-event image extraction",
            "Automatic event extraction",
            "AI agenda scanning for event parts",
            "Custom AI prompts",
            "Instant translation",
            "AI style generation",
            "AI content generation",
            "AI flyer generation",
            "AI graphic text processing",
            "WhatsApp event creation",
            "REST API for AI agents",
            "OpenAPI 3.0 specification",
            "llms.txt AI discovery",
            "agents.json workflows",
            "Webhook notifications",
            "AI powered"
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
        /* For AI features "The Spark" styles. The shared es-* motion system lives in
           marketing.css; this holds the AI glow gradient, the drifting generation
           card, and the twinkling-sparkles motif. */
        .text-gradient-ai {
            background: linear-gradient(135deg, #2563eb, #0ea5e9, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-ai {
            background: linear-gradient(135deg, #60a5fa, #38bdf8, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-ai-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-ai-float { animation: es-ai-float 6s ease-in-out infinite; }

        /* Twinkling-sparkles motif: AI generation shimmer. */
        .es-sparkfield { display: flex; align-items: center; }
        .es-spark {
            flex: 0 0 auto;
            width: 6px; height: 6px; border-radius: 9999px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.95), rgba(56, 189, 248, 0) 70%);
            animation: es-spark-tw var(--sp-dur, 2.4s) ease-in-out infinite;
            animation-delay: var(--sp-delay, 0s);
        }
        @keyframes es-spark-tw {
            0%, 100% { opacity: 0.15; transform: scale(0.55); }
            50% { opacity: 1; transform: scale(1.25); box-shadow: 0 0 10px rgba(56, 189, 248, 0.6); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-ai-float, .es-spark { animation: none !important; }
            .es-spark { opacity: 0.5; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: AI-powered features                                 -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.28), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Twinkling sparkles along the bottom edge -->
            <div class="es-sparkfield absolute bottom-0 left-0 right-0 hidden h-20 items-center justify-center gap-4 px-8 pb-6 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 30; $i++)
                    @php $dur = 1.8 + ($i % 6) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                    <span class="es-spark" style="--sp-dur: {{ $dur }}s; --sp-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">AI-Powered</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">AI-powered</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-ai">features</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From smart import to content generation and visual branding. Paste text, drop an image, or let AI write your descriptions and create your style.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Try it free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.ai_import') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the AI Import guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Generate content and style                                -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Generate content and style with <span class="text-gradient-ai">AI</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">AI creates your visual identity and writes your content.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- AI Style Generation (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                                    Style Generation
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Generate your brand style</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">AI creates a complete visual identity for your schedule - profile image, header image, background image, accent color, and font. Add style instructions to guide the look.</p>
                                <div class="mb-4 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Profile image</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Header</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Background</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Accent color</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Font</span>
                                </div>
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="w-full max-w-xs rounded-2xl border border-gray-200 bg-gray-100 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Generated elements</div>
                                    <div class="space-y-2.5">
                                        @foreach (['Profile image', 'Header image', 'Background image', 'Accent color', 'Font'] as $ei => $el)
                                            <div class="es-ai-field flex items-center gap-3" style="--i: {{ $ei }};">
                                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sky-500/20">
                                                    @if ($el === 'Accent color')
                                                        <div class="h-3.5 w-3.5 rounded-full bg-sky-400"></div>
                                                    @else
                                                        <svg aria-hidden="true" class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    @endif
                                                </div>
                                                <span class="text-sm text-gray-900 dark:text-white">{{ $el }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Write schedule descriptions -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Content
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Write schedule descriptions</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Generate polished short and full descriptions for your schedule. Save custom prompts to maintain your tone.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 flex items-center gap-2">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                <span class="text-xs text-gray-500 dark:text-gray-400">AI-generated</span>
                            </div>
                            <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">A vibrant live music hub featuring jazz, blues, and soul performances every week...</div>
                        </div>
                        <div class="mt-4"><span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span></div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Generate event details -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                            Content
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Generate event details</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">AI writes event category, short description, and full description based on the event name and context.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-white/60">Category:</span><span class="text-gray-900 dark:text-white">Music</span></div>
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-white/60">Short:</span><span class="text-gray-900 dark:text-white">Live jazz trio</span></div>
                                <div class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-300">An evening of smooth jazz featuring...</div>
                            </div>
                        </div>
                        <div class="mt-4"><span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span></div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- AI text for graphic emails -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">AI text for graphic emails</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Process your event list text through AI before sending graphic emails. Add flair, reformat, or customize.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 flex items-center gap-2">
                                <svg aria-hidden="true" class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                <span class="text-xs text-gray-500 dark:text-gray-400">AI-processed</span>
                            </div>
                            <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">This week's highlights...<br><span class="text-amber-600 dark:text-amber-300">Jazz Night - Fri 8pm</span></div>
                        </div>
                        <div class="mt-4"><span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span></div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Generate event flyers -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                            AI Flyer Generation
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Generate event flyers</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Enter event details and let AI create a professional, eye-catching flyer. Customize the style and regenerate until it's perfect.</p>
                        <div class="mt-auto overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="bg-gradient-to-br from-teal-600 to-cyan-700 p-4 text-center text-white">
                                <div class="mb-0.5 text-sm font-bold">Summer Jazz Festival</div>
                                <div class="mb-1 text-[10px] opacity-80">Saturday, July 12, 2025</div>
                                <div class="mx-auto mb-1 h-0.5 w-8 bg-white/40"></div>
                                <div class="text-[10px] opacity-70">Central Park Amphitheater</div>
                            </div>
                            <div class="flex flex-wrap gap-2 p-3">
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">One-click</span>
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Custom style</span>
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Regenerate</span>
                            </div>
                        </div>
                        <div class="mt-4"><span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span></div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Built for AI agents (dark band)                          -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-sparkfield absolute bottom-0 left-0 right-0 flex h-16 items-center justify-center gap-4 px-8 pb-3 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 28; $i++)
                        @php $dur = 1.8 + ($i % 6) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                        <span class="es-spark" style="--sp-dur: {{ $dur }}s; --sp-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/15 px-3 py-1.5 text-sm font-medium text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        API for AI Agents
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-white md:text-4xl lg:text-5xl">Built for <span class="text-gradient-ai">AI agents</span></h2>
                    <p class="mb-8 text-lg text-gray-300">A full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. Let AI agents create, update, and manage events programmatically.</p>
                    <div class="mb-8 flex flex-wrap gap-3">
                        @foreach (['REST API', 'OpenAPI 3.0', 'llms.txt', 'agents.json', 'Webhooks', 'Smart creation'] as $pill)
                            <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-sm font-medium text-gray-300">{{ $pill }}</span>
                        @endforeach
                    </div>
                    <a href="{{ marketing_url('/for-ai-agents') }}" class="inline-flex items-center gap-2 text-lg font-medium text-blue-300 transition-all hover:gap-3 hover:text-blue-200">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>

                <div class="flex flex-col items-center gap-6" data-reveal style="--reveal-delay: 0.12s;" aria-hidden="true">
                    <div class="w-full max-w-md">
                        <div class="mb-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center rounded px-2 py-0.5 font-mono text-xs font-bold text-emerald-300" style="background: rgba(16,185,129,0.2);">POST</span>
                                <span class="font-mono text-sm text-gray-400">/api/events</span>
                            </div>
                            <div class="rounded-lg bg-black/30 p-3 font-mono text-xs leading-relaxed text-gray-300">
                                <span class="text-gray-500">{</span><br>
                                &nbsp;&nbsp;<span class="text-blue-400">"name"</span>: <span class="text-emerald-400">"Jazz Night"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-400">"date"</span>: <span class="text-emerald-400">"2025-03-15"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-400">"venue"</span>: <span class="text-emerald-400">"Blue Note"</span><br>
                                <span class="text-gray-500">}</span>
                            </div>
                        </div>
                        <div class="my-2 flex justify-center">
                            <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </div>
                        <div class="rounded-2xl border border-blue-400/30 bg-gradient-to-br from-blue-500/10 to-sky-500/10 p-5">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center rounded px-2 py-0.5 font-mono text-xs font-bold text-emerald-300" style="background: rgba(16,185,129,0.2);">201</span>
                                <span class="text-sm font-medium text-emerald-400">Created</span>
                            </div>
                            <div class="rounded-lg bg-black/20 p-3 font-mono text-xs leading-relaxed text-gray-300">
                                <span class="text-gray-500">{</span><br>
                                &nbsp;&nbsp;<span class="text-blue-400">"id"</span>: <span class="text-amber-400">4829</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-400">"name"</span>: <span class="text-emerald-400">"Jazz Night"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-400">"status"</span>: <span class="text-emerald-400">"published"</span><br>
                                <span class="text-gray-500">}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-center gap-3">
                        @foreach (['llms.txt', 'agents.json', 'openapi.json'] as $file)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 font-mono text-sm text-gray-300">
                                <svg aria-hidden="true" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                {{ $file }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Import and parse events                                  -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Import and parse events with <span class="text-gradient-ai">AI</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Paste text, drop an image, or send a WhatsApp message.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- Parse any format (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    Smart Parsing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Parse any format</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Flyers, emails, agendas, and setlists. Paste the text or drop an image and AI extracts event name, date, time, venue, and description. Multi-event images are parsed into individual events automatically.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Text parsing</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Image recognition</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any language</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Agendas & setlists</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="mb-4 max-w-xs rounded-2xl border border-gray-200 bg-gray-200 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="mb-2 text-xs text-gray-400">Paste or drop</div>
                                        <div class="font-mono text-sm leading-relaxed text-gray-600 dark:text-gray-300">Jazz Night at Blue Note<br>Friday, March 15 at 8pm<br>Featuring the Sarah Johnson Trio<br>Tickets: $25 at the door</div>
                                    </div>
                                    <div class="my-2 flex justify-center">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                    </div>
                                    <div class="max-w-xs rounded-2xl border border-blue-400/30 bg-gradient-to-br from-blue-500/20 to-sky-500/20 p-4">
                                        <div class="mb-2 text-xs text-blue-700 dark:text-blue-300">Extracted</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="es-ai-field flex justify-between" style="--i: 0;"><span class="text-blue-700 dark:text-white/60">Name:</span><span class="text-blue-900 dark:text-white">Jazz Night</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 1;"><span class="text-blue-700 dark:text-white/60">Date:</span><span class="text-blue-900 dark:text-white">Mar 15, 8:00 PM</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 2;"><span class="text-blue-700 dark:text-white/60">Venue:</span><span class="text-blue-900 dark:text-white">Blue Note</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 3;"><span class="text-blue-700 dark:text-white/60">Talent:</span><span class="text-blue-900 dark:text-white">Sarah Johnson Trio</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Instant translation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>
                            Translation
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Instant translation</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Translate your entire schedule automatically. Choose which language to translate into, and visitors can switch between your language and the translation. Support for 11 languages including English, Spanish, French, German, and more.</p>
                        <div class="mt-auto flex flex-wrap justify-center gap-2" aria-hidden="true">
                            @foreach (['EN', 'ES', 'FR', 'DE', 'IT', 'PT', 'NL', 'HE', 'AR'] as $li => $lang)
                                <span class="es-ai-field inline-flex items-center rounded-lg bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white" style="--i: {{ $li }};">{{ $lang }}</span>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Auto-link venues & talent -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Smart Linking
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Auto-link venues & talent</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">AI matches parsed events to existing venues and performers in your schedule using fuzzy matching.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="es-ai-field mb-3 flex items-center gap-3" style="--i: 0;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <div><div class="text-sm font-medium text-gray-900 dark:text-white">Blue Note Jazz Club</div><div class="text-xs text-emerald-600 dark:text-emerald-400">Matched to existing venue</div></div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3" style="--i: 1;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <div><div class="text-sm font-medium text-gray-900 dark:text-white">Sarah Johnson Trio</div><div class="text-xs text-emerald-600 dark:text-emerald-400">Matched to existing talent</div></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Any input works (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Multiple Formats
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Any input works</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Text, images, screenshots, and flyers. Drop it in and AI figures out the rest.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @foreach ([['Text', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'], ['Images', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'], ['Flyers', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'], ['Agendas', 'M4 6h16M4 10h16M4 14h16M4 18h16']] as $fi => [$label, $path])
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: {{ $fi }};">
                                        <svg aria-hidden="true" class="mx-auto mb-2 h-8 w-8 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}" /></svg>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Scan a printed agenda (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                    Agenda Scanning
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Scan a printed agenda</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Point your camera at a printed agenda or upload an image. AI reads each line item and populates your event's parts automatically - sessions, acts, segments, and more.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Camera capture</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Image upload</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-populate parts</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div>
                                    <div class="mb-4 max-w-xs rounded-2xl border border-gray-200 bg-gray-200 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="mb-2 text-xs text-gray-400">Printed agenda</div>
                                        <div class="font-mono text-sm leading-relaxed text-gray-600 dark:text-gray-300">9:00 AM - Opening Keynote<br>10:30 AM - Panel Discussion<br>12:00 PM - Lunch Break<br>1:30 PM - Workshop A</div>
                                    </div>
                                    <div class="my-2 flex justify-center">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                    </div>
                                    <div class="max-w-xs rounded-2xl border border-amber-400/30 bg-gradient-to-br from-amber-500/20 to-orange-500/20 p-4">
                                        <div class="mb-2 text-xs text-amber-700 dark:text-amber-300">Event parts</div>
                                        <div class="space-y-1.5">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-amber-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-xs text-gray-900 dark:text-white">Opening Keynote</span><span class="ml-auto text-[10px] text-amber-700 dark:text-amber-300">9:00 AM</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-amber-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-orange-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Panel Discussion</span><span class="ml-auto text-[10px] text-gray-500 dark:text-gray-400">10:30 AM</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-amber-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Workshop A</span><span class="ml-auto text-[10px] text-gray-500 dark:text-gray-400">1:30 PM</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Guide the AI (custom prompts) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Custom Prompts
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Guide the AI</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Add custom instructions to help AI understand your agenda format. Set prompts per event or as a default for your entire schedule.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Custom prompt</div>
                            <div class="rounded-lg border border-blue-400/20 bg-blue-500/10 p-3">
                                <div class="font-mono text-xs leading-relaxed text-blue-900 dark:text-blue-200">"Each line is a session.<br>Format: time - speaker - topic.<br>Ignore lunch breaks."</div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Per-event or schedule-wide
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- WhatsApp (3 cols) -->
                <div class="es-bento group relative md:col-span-2 lg:col-span-3" data-tilt="2.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    WhatsApp
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Create events via WhatsApp</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Send a text message or photo of a flyer to your schedule's WhatsApp number. AI parses the details, generates a flyer, and adds the event automatically.</p>
                                <div class="mb-4 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Text or photo</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-flyer</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-curate</span>
                                </div>
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">Enterprise</span>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="w-full max-w-xs">
                                    <div class="es-ai-field mb-3 rounded-2xl border border-gray-200 bg-gray-200 p-4 dark:border-white/10 dark:bg-[#0f0f14]" style="--i: 0;">
                                        <div class="mb-2 flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Incoming</span>
                                        </div>
                                        <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">Jazz Night at Blue Note<br>Friday March 15 at 8pm</div>
                                    </div>
                                    <div class="es-ai-field rounded-2xl border border-emerald-400/30 bg-gradient-to-br from-emerald-500/20 to-green-500/20 p-4" style="--i: 1;">
                                        <div class="mb-2 flex items-center gap-2">
                                            <svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Event created</span>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">Jazz Night added to your schedule with auto-generated flyer.</div>
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
    <!-- 5. How it works                                             -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Pick a feature', 'Import events, generate content, create your style, or process graphic text.'],
            ['2', 'Provide input', 'Paste text, drop an image, or add custom instructions.'],
            ['3', 'AI does the work', 'AI generates the result in seconds.'],
            ['4', 'Review and save', 'Preview, edit if needed, and apply.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>How it <span class="text-gradient-ai">works</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Four steps from input to output.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">{{ $num }}</div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Powered by Gemini & OpenAI                               -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl border border-blue-500/30 bg-gradient-to-br from-blue-500/20 to-sky-500/20" data-reveal>
                    <svg aria-hidden="true" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Powered by Google Gemini & OpenAI</h2>
                <p class="mx-auto max-w-2xl text-xl text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Gemini handles text parsing and generation, while OpenAI DALL-E creates stunning images.</p>
            </div>

            <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="80">
                @foreach ([['Best of both', 'Gemini for text, OpenAI for image generation'], ['Fast', 'Events parsed and content generated in seconds'], ['Multimodal', 'Works with text, images, screenshots, and flyers'], ['Any language', 'Parse and generate content in any language']] as [$stat, $sub])
                    <div data-reveal class="ap-card rounded-2xl border border-gray-200 bg-white p-6 text-center dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stat }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sub }}</p>
                    </div>
                @endforeach
            </div>

            <div class="text-center" data-reveal>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Selfhosted users bring their own API keys.
                    <x-link href="{{ route('marketing.docs.selfhost.ai') }}">Learn more</x-link>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Guide & next feature                                     -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <a href="{{ route('marketing.docs.creating_events') }}" data-reveal class="group block">
                    <div class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Read the guide</h3>
                        <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to use all AI-powered features.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                            Read guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </div>
                </a>

                <a href="{{ route('marketing.newsletters') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-cyan-100 p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-sky-900 dark:to-cyan-900 lg:p-10">
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-300">Newsletters</h3>
                        <p class="mb-4 text-lg text-gray-500 dark:text-white/80">Send branded newsletters to followers and ticket buyers with drag-and-drop building and A/B testing.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-500 transition-all group-hover:gap-3 dark:text-sky-400">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </div>
                </a>

                <div data-reveal class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-curators', 'Curators'], ['/for-musicians', 'Musicians'], ['/for-venues', 'Venues']] as [$href, $label])
                            <a href="{{ marketing_url($href) }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-ai">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about AI features.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What formats does the AI parser support?', 'The AI can parse plain text, images (JPEG, PNG, GIF, WebP), screenshots, flyers, agendas, and setlists. It handles both single events and multi-event documents, extracting each event individually.'],
                    ['Which AI features are free vs. Enterprise?', 'AI event parsing and instant translation are available on all plans including the free tier. Style generation, content generation, flyer generation, graphic text processing, and WhatsApp event creation are Enterprise features.'],
                    ['How accurate is the AI?', 'The AI is highly accurate for standard event formats. You always get to review and edit the results before saving. You can also add custom prompts to guide the AI for your specific format.'],
                    ['What can AI Style Generation create?', 'AI Style Generation creates a complete visual identity for your schedule including profile image, header image, background image, accent color, and font. You can add custom style instructions to guide the look and regenerate individual elements.'],
                    ['How does WhatsApp event creation work?', 'Send a text message or photo of a flyer to your schedule\'s WhatsApp number. AI parses the details, auto-generates a flyer, and adds the event to your schedule automatically.'],
                    ['What languages are supported?', 'The AI can parse events in any language and extract details correctly. The translation feature supports 11 languages including English, Spanish, French, German, Italian, Portuguese, Dutch, Hebrew, Arabic, Estonian, and Russian.'],
                    ['Can AI agents use Event Schedule programmatically?', 'Yes. Event Schedule provides a full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. AI agents can create, update, and manage events programmatically with smart creation features and webhook notifications.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
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
    <!-- 9. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Event Graphics" description="Auto-generated shareable images for Instagram, WhatsApp, and email" :url="marketing_url('/features/event-graphics')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat on any schedule automatically" :url="marketing_url('/features/recurring-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Collect additional info from attendees with custom form fields" :url="marketing_url('/features/custom-fields')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="API for AI Agents" description="REST API with OpenAPI spec, llms.txt, and agents.json for AI agent workflows" :url="marketing_url('/for-ai-agents')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale                                                  -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-sparkfield absolute bottom-0 left-0 right-0 flex h-14 items-center justify-center gap-4 px-8 pb-3 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $dur = 1.8 + ($i % 6) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                            <span class="es-spark" style="--sp-dur: {{ $dur }}s; --sp-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Let AI do the <span class="text-gradient-ai">heavy lifting</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        From smart import to content generation and visual branding. AI handles the tedious work so you can focus on your events.
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

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
