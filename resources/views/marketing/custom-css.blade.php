<x-marketing-layout>
    <x-slot name="title">Custom CSS | Advanced Schedule Styling - Event Schedule</x-slot>
    <x-slot name="description">Write your own CSS to customize every pixel of your schedule. Override defaults, add animations, and create a look that's uniquely yours.</x-slot>
    <x-slot name="breadcrumbTitle">Custom CSS</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom CSS",
        "description": "Write your own CSS to customize every pixel of your schedule. Override defaults, add animations, and create a look that's uniquely yours.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Write custom CSS",
            "Sanitized for safety",
            "Works with built-in styling",
            "Modern CSS support"
        ],
        "offers": {
            "@type": "Offer",
            "price": "5",
            "priceCurrency": "USD",
            "description": "Available on Pro plan"
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
                "name": "How do I add Custom CSS to my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to Admin Panel, then Profile, then Edit and scroll to the styling section. You'll find a Custom CSS text area where you can write your CSS rules. Changes take effect immediately on save."
                }
            },
            {
                "@type": "Question",
                "name": "Is Custom CSS secure?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while allowing modern CSS properties for styling."
                }
            },
            {
                "@type": "Question",
                "name": "Can I use Custom CSS with the built-in styling options?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Custom CSS works alongside all built-in styling options. Use the visual tools for quick changes like colors and fonts, and CSS for fine-tuning details like spacing, shadows, and animations."
                }
            },
            {
                "@type": "Question",
                "name": "Which plan includes Custom CSS?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom CSS is available on Pro and Enterprise plans, as well as selfhosted installations. Free plans include the full visual styling suite (colors, fonts, gradients, backgrounds)."
                }
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
        /* For custom-css "The Stylesheet" styles. The shared es-* motion system lives in
           marketing.css; this holds the blue/cyan glow gradient, the drifting code editor,
           and the curly-brace motif (CSS rules cascading across the page). */
        .text-gradient-css {
            background: linear-gradient(135deg, #2563eb, #0ea5e9, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-css {
            background: linear-gradient(135deg, #60a5fa, #38bdf8, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-css-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-css-float { animation: es-css-float 6s ease-in-out infinite; }

        /* Curly-brace motif: CSS braces blink in a wave, like style rules cascading
           across your schedule. */
        .es-braces { display: flex; align-items: center; }
        .es-brace {
            flex: 0 0 auto; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 800; line-height: 1; color: rgba(37, 99, 235, 0.85);
            animation: es-brace-blink var(--bc-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--bc-delay, 0s);
        }
        @keyframes es-brace-blink {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.5)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-css-float, .es-brace, .animate-pulse-slow { animation: none !important; }
            .es-brace { opacity: 0.55; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: custom css                                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.28), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Curly-brace motif along the bottom edge -->
            <div class="es-braces absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-45 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 16; $i++)
                    @php $g = ['{ }', '{', '}', '{ }'][$i % 4]; $sz = [26, 20, 20, 32][$i % 4]; $dur = 2.4 + ($i % 4) * 0.35; $delay = ($i % 8) * 0.28; @endphp
                    <span class="es-brace" style="font-size: {{ $sz }}px; --bc-dur: {{ $dur }}s; --bc-delay: {{ $delay }}s;">{{ $g }}</span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Custom CSS</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Style it</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-css">your way</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Write your own CSS to customize every pixel of your schedule. Override defaults, add animations, and create a look that's uniquely yours.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.schedule_styling') }}#custom-css" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Custom CSS guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Pixel-perfect control, <span class="text-gradient-css">in your hands</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Full control (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    Full Control
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Write real CSS</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">No drag-and-drop limitations. Write actual CSS to override any style, add animations, adjust spacing, or completely transform how your schedule looks.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Override any style</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Modern CSS supported</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-72" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-red-400"></div>
                                        <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                                        <div class="h-3 w-3 rounded-full bg-green-400"></div>
                                        <span class="ml-2 text-xs text-gray-400">custom.css</span>
                                    </div>
                                    <div class="space-y-1 font-mono text-sm">
                                        <div class="text-blue-600 dark:text-blue-400">.event-card {</div>
                                        <div class="pl-4 text-cyan-600 dark:text-cyan-400">border-radius: 1rem;</div>
                                        <div class="pl-4 text-cyan-600 dark:text-cyan-400">box-shadow: 0 4px 20px</div>
                                        <div class="pl-8 text-cyan-600 dark:text-cyan-400">rgba(0,0,0,0.1);</div>
                                        <div class="text-blue-600 dark:text-blue-400">}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Secure -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Secure
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sanitized for safety</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while modern CSS properties are fully supported.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach (['No XSS vulnerabilities', 'No external requests', 'Modern CSS supported'] as $si => $item)
                                <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: {{ $si }};">
                                    <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Composable -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Composable
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Layer on top</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Custom CSS works alongside all built-in styling options. Use the visual tools for quick changes and CSS for the finishing touches.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="space-y-2 text-sm">
                                <div class="es-ai-field flex items-center gap-2" style="--i: 0;">
                                    <div class="h-4 w-4 rounded bg-blue-500"></div>
                                    <span class="text-gray-600 dark:text-gray-300">Colors &amp; gradients</span>
                                </div>
                                <div class="es-ai-field flex items-center gap-2" style="--i: 1;">
                                    <div class="h-4 w-4 rounded bg-cyan-500"></div>
                                    <span class="text-gray-600 dark:text-gray-300">Fonts &amp; typography</span>
                                </div>
                                <div class="es-ai-field flex items-center gap-2" style="--i: 2;">
                                    <div class="h-4 w-4 rounded bg-sky-500"></div>
                                    <span class="text-gray-600 dark:text-gray-300">+ Your Custom CSS</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Pro feature (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                    Pro Feature
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Included with Pro</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Custom CSS is part of the Pro plan, alongside white-label branding, event graphics, ticketing, and more. Start free and upgrade when you need advanced styling.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @php
                                    $proTiles = [
                                        ['Custom CSS', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                                        ['White Label', 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                                        ['Event Graphics', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                        ['Ticketing', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                                    ];
                                @endphp
                                @foreach ($proTiles as $ti => $tile)
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: {{ $ti }};">
                                        <svg aria-hidden="true" class="mx-auto mb-2 h-8 w-8 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tile[1] }}" />
                                        </svg>
                                        <div class="text-sm text-blue-600 dark:text-blue-400">{{ $tile[0] }}</div>
                                    </div>
                                @endforeach
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
    <!-- 3. What you can do (dark band)                              -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-braces absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 12; $i++)
                        @php $g = ['{ }', '{', '}', '{ }'][$i % 4]; $sz = [26, 20, 20, 32][$i % 4]; $dur = 2.4 + ($i % 4) * 0.35; $delay = ($i % 8) * 0.28; @endphp
                        <span class="es-brace" style="font-size: {{ $sz }}px; --bc-dur: {{ $dur }}s; --bc-delay: {{ $delay }}s;">{{ $g }}</span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>What you can do with <span class="text-gradient-css">Custom CSS</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Go beyond the built-in styling options.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                    @php
                        $cssUseCases = [
                            ['Custom Layouts', 'Adjust card sizes, spacing, and grid behavior for a unique layout.', 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                            ['Animations', 'Add hover effects, transitions, and CSS animations to your events.', 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Fine-Tuning', 'Adjust borders, shadows, spacing, and typography details.', 'M4 6h16M4 12h16M4 18h7'],
                            ['Brand Matching', 'Match your website\'s exact styles for a seamless embed experience.', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ];
                    @endphp
                    @foreach ($cssUseCases as $uc)
                        <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/30 hover:bg-white/[0.06]">
                            <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                                <svg aria-hidden="true" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $uc[2] }}" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ $uc[0] }}</h3>
                            <p class="text-sm text-gray-300">{{ $uc[1] }}</p>
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
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-cyan-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.schedule_styling') }}#custom-css" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Read the guide</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to write Custom CSS for your schedule.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                        Read guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/white-label') }}" data-reveal class="group flex flex-col rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-cyan-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-blue-900 dark:to-cyan-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">White Label</h3>
                    <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Remove Event Schedule branding for a fully branded experience.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-blue-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-blue-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-art-galleries') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-blue-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Art Galleries</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-blue-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-music-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-blue-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Music Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-blue-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-css">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about Custom CSS.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I add Custom CSS to my schedule?', 'Go to Admin Panel, then Profile, then Edit and scroll to the styling section. You\'ll find a Custom CSS text area where you can write your CSS rules. Changes take effect immediately on save.'],
                    ['Is Custom CSS secure?', 'Yes. All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while allowing modern CSS properties for styling.'],
                    ['Can I use Custom CSS with the built-in styling options?', 'Yes. Custom CSS works alongside all built-in styling options. Use the visual tools for quick changes like colors and fonts, and CSS for fine-tuning details like spacing, shadows, and animations.'],
                    ['Which plan includes Custom CSS?', 'Custom CSS is available on Pro and Enterprise plans, as well as selfhosted installations. Free plans include the full visual styling suite (colors, fonts, gradients, backgrounds).'],
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
                        name="White Label"
                        description="Remove Event Schedule branding for a fully branded experience"
                        :url="marketing_url('/features/white-label')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your schedule on any website with an iframe"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Event Graphics"
                        description="Generate shareable images for social media"
                        :url="marketing_url('/features/event-graphics')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                    <div class="es-braces absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 10; $i++)
                            @php $g = ['{ }', '{', '}', '{ }'][$i % 4]; $sz = [26, 20, 20, 32][$i % 4]; $dur = 2.4 + ($i % 4) * 0.35; $delay = ($i % 8) * 0.28; @endphp
                            <span class="es-brace" style="font-size: {{ $sz }}px; --bc-dur: {{ $dur }}s; --bc-delay: {{ $delay }}s;">{{ $g }}</span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-css">customize?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Upgrade to Pro and unlock Custom CSS for complete control over your schedule's appearance.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
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

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
