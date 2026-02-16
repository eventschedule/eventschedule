<x-marketing-layout>
    <x-slot name="title">API for AI Agents & Developers - Event Schedule</x-slot>
    <x-slot name="description">Full REST API with OpenAPI spec, llms.txt, and agents.json for AI agents and developers. Create events, manage tickets, and automate scheduling programmatically. Zero platform fees.</x-slot>
    <x-slot name="keywords">event API, AI agent scheduling, REST API events, OpenAPI event management, developer event platform, programmatic event creation, AI scheduling API, event automation API, llms.txt, agents.json, LLM API</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For AI Agents</x-slot>

    <style {!! nonce_attr() !!}>
        .agent-glow-text {
            background: linear-gradient(135deg, #06b6d4, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(6, 182, 212, 0.3);
        }
        .dark .agent-glow-text {
            background: linear-gradient(135deg, #22d3ee, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(34, 211, 238, 0.3);
        }

        .noise::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
        }

        @keyframes reveal-up {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-reveal { animation: reveal-up 0.8s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        .dark .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            background-size: 200% 100%;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-reveal,
            .animate-shimmer,
            .animate-pulse-slow,
            .animate-float {
                animation: none !important;
            }
            .animate-reveal {
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule API",
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "Web",
        "description": "Full REST API with OpenAPI spec for AI agents and developers to create events, manage tickets, and automate scheduling programmatically.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free for read operations. Pro plan for write operations."
        },
        "featureList": [
            "Full REST API with JSON responses",
            "OpenAPI 3.0 specification",
            "llms.txt and llms-full.txt LLM discovery files",
            "agents.json workflow definitions",
            "Schedule and event management",
            "Ticket creation and sales tracking",
            "Recurring event patterns",
            "Auto-translation to 11 languages",
            "Rate limiting at 300 GET requests per minute",
            "API key authentication"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
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
                "name": "What is llms.txt?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "llms.txt is an emerging standard that helps AI models discover and understand your platform. Event Schedule provides both llms.txt (a concise summary for initial discovery and routing) and llms-full.txt (a complete, self-contained reference so agents can work without following additional links)."
                }
            },
            {
                "@type": "Question",
                "name": "What can I do with the Event Schedule API?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can create, read, update, and delete schedules, events, tickets, sub-schedules, and sales. The API supports smart event creation with venue auto-resolution, member matching, recurring event patterns, and auto-translation to 11 languages."
                }
            },
            {
                "@type": "Question",
                "name": "Is the Event Schedule API free to use?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Read operations are free on any Pro schedule. Write operations (creating and updating events, tickets, etc.) require a Pro plan. Ticket sales have zero platform fees - you keep 100% of revenue minus payment processing."
                }
            },
            {
                "@type": "Question",
                "name": "How does API authentication work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Authentication uses API keys passed via the X-API-Key header. Generate your API key from your account settings after signing up. Read-only endpoints on public schedules don't require authentication."
                }
            }
        ]
    }
    </script>
    </x-slot>

    {{-- Section 1: Hero --}}
    <section class="relative min-h-screen flex items-center overflow-hidden bg-white dark:bg-[#0a0a0f] noise">
        {{-- Animated gradient orbs --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-gradient-to-r from-cyan-600/30 to-cyan-500/30 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-gradient-to-r from-emerald-600/20 to-teal-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-cyan-600/10 to-emerald-600/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 3s;"></div>
        </div>

        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 grid-pattern bg-[size:60px_60px]"></div>

        <div class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 text-center">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For AI Agents & Developers</span>
            </div>

            {{-- Main headline --}}
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight mb-6 animate-reveal delay-100" style="opacity: 0;">
                <span class="block text-gray-900 dark:text-white">Schedule events.</span>
                <span class="block agent-glow-text pb-2">Programmatically.</span>
            </h1>

            {{-- Subheadline --}}
            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                A full REST API with OpenAPI spec. Let your AI agent create events, manage tickets, and automate scheduling with simple HTTP requests.
            </p>

            {{-- CTAs --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.developer.api') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-cyan-600 to-emerald-600 rounded-2xl overflow-hidden transition-all hover:scale-105 hover:shadow-2xl hover:shadow-cyan-500/25">
                    <span class="absolute inset-0 animate-shimmer"></span>
                    <span class="relative z-10 flex items-center gap-2">
                        Read API Docs
                        <svg aria-hidden="true" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
                <a href="/api/openapi.json" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-700 dark:text-gray-200 border-2 border-gray-300 dark:border-white/20 rounded-2xl hover:border-cyan-500 dark:hover:border-cyan-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all">
                    <svg aria-hidden="true" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    OpenAPI Spec
                </a>
            </div>

            {{-- Tech tags --}}
            <div class="flex flex-wrap justify-center gap-3 mt-12 animate-reveal delay-300" style="opacity: 0;">
                @foreach(['OpenAI', 'Claude', 'LangChain', 'Custom Agents', 'REST'] as $tag)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 text-sm border border-gray-200 dark:border-white/10">{{ $tag }}</span>
                @endforeach
                <a href="/llms.txt" class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-sm border border-cyan-200 dark:border-cyan-500/20 hover:bg-cyan-100 dark:hover:bg-cyan-500/20 transition-colors">llms.txt</a>
                <a href="/llms-full.txt" class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-sm border border-cyan-200 dark:border-cyan-500/20 hover:bg-cyan-100 dark:hover:bg-cyan-500/20 transition-colors">llms-full.txt</a>
                <a href="/.well-known/agents.json" class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-sm border border-cyan-200 dark:border-cyan-500/20 hover:bg-cyan-100 dark:hover:bg-cyan-500/20 transition-colors">agents.json</a>
            </div>
        </div>
    </section>
    {{-- Stats Section --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-cyan-400 mb-2">40+ hours</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">to build event management from scratch</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-amber-400 mb-2">0%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on ticket sales</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-sky-400 mb-2">< 5 min</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">to your first API call</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Features Bento Grid --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Everything your agent needs
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    A complete toolkit for programmatic event management.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Card 1: Full REST API (2-col span) --}}
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                REST API
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Full REST API</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Create, read, update, and delete schedules, events, tickets, and sub-schedules. Standard HTTP methods, JSON responses, and predictable URL structure.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">JSON responses</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">API key auth</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Rate limiting</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 max-w-xs">
                                    <div class="text-xs text-gray-400 mb-2 font-mono">POST /api/events</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        {<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"name"</span>: <span class="text-emerald-600 dark:text-emerald-400">"AI Meetup"</span>,<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"starts_at"</span>: <span class="text-emerald-600 dark:text-emerald-400">"2026-06-15"</span>,<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"venue"</span>: <span class="text-emerald-600 dark:text-emerald-400">"Tech Hub SF"</span><br>
                                        }
                                    </div>
                                </div>
                                <div class="flex justify-center my-2">
                                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 max-w-xs">
                                    <div class="text-xs text-emerald-500 mb-2 font-mono">201 Created</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        {<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"id"</span>: <span class="text-amber-600 dark:text-amber-400">42</span>,<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"name"</span>: <span class="text-emerald-600 dark:text-emerald-400">"AI Meetup"</span>,<br>
                                        &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"url"</span>: <span class="text-emerald-600 dark:text-emerald-400">"..."</span><br>
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Smart Event Creation --}}
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        Smart Creation
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Smart event creation</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Send event details and the API auto-resolves venues, members, and categories. No need to look up IDs first.</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Venue auto-resolution
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Member matching
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Sub-schedule assignment
                        </div>
                    </div>
                </div>

                {{-- Card 3: One Link for Everything --}}
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Shareable URL
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for everything</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Every schedule gets a public URL your agent can share. Events, tickets, and iCal feeds all accessible from one link.</p>
                    <div class="bg-gray-200 dark:bg-[#0f0f18] rounded-xl border border-gray-200 dark:border-white/10 p-3">
                        <div class="text-sm font-mono text-sky-600 dark:text-sky-300 truncate">your-schedule.eventschedule.com</div>
                    </div>
                </div>

                {{-- Card 4: Ticket Management (2-col span) --}}
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Ticketing
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Ticket management</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Create ticket types, set prices, and track sales through the API. Zero platform fees on all ticket sales.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multiple ticket types</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Zero platform fees</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Sales tracking</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="space-y-3 max-w-xs">
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">General Admission</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">50 available</div>
                                    </div>
                                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$25</div>
                                </div>
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">VIP Access</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">10 available</div>
                                    </div>
                                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$75</div>
                                </div>
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Student</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">25 available</div>
                                    </div>
                                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$15</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 5: Recurring Events --}}
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Recurring events</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Set up repeating events with flexible patterns. Daily, weekly, monthly, or custom recurrence rules.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-sm border border-blue-200 dark:border-blue-500/20">Every Monday</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-sm border border-blue-200 dark:border-blue-500/20">1st of month</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-sm border border-blue-200 dark:border-blue-500/20">Every 2 weeks</span>
                    </div>
                </div>

                {{-- Card 6: Multi-Language --}}
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        i18n
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Multi-language</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Events auto-translate to 11 languages. Reach a global audience without extra work.</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-mono text-teal-600 dark:text-teal-400 w-8">EN</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">AI Developer Meetup</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-mono text-teal-600 dark:text-teal-400 w-8">ES</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Encuentro de desarrolladores IA</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-mono text-teal-600 dark:text-teal-400 w-8">DE</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">KI-Entwicklertreffen</span>
                        </div>
                    </div>
                </div>

                {{-- Card 7: OpenAPI Spec --}}
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-emerald-100 dark:from-cyan-900 dark:to-emerald-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        OpenAPI 3.0
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">OpenAPI spec</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Download the OpenAPI 3.0 spec to auto-generate client libraries in any language.</p>
                    <div class="bg-gray-200 dark:bg-[#0f0f18] rounded-xl border border-gray-200 dark:border-white/10 p-3 mb-4">
                        <div class="text-sm font-mono text-cyan-600 dark:text-cyan-300 truncate">/api/openapi.json</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs border border-cyan-200 dark:border-cyan-500/20">Python</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs border border-cyan-200 dark:border-cyan-500/20">JavaScript</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs border border-cyan-200 dark:border-cyan-500/20">Go</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs border border-cyan-200 dark:border-cyan-500/20">PHP</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- AI-Native Discovery Files --}}
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    AI-native discovery
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Standard discovery files so AI agents can find and understand the API without human help.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- llms.txt --}}
                <a href="/llms.txt" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-950/50 dark:to-teal-950/50 border border-cyan-200 dark:border-cyan-800/50 p-6 hover:border-cyan-400 dark:hover:border-cyan-600 transition-colors">
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-100 dark:bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 text-xs font-mono font-medium mb-4">llms.txt</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Quick overview</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Concise summary for initial discovery and routing. Helps agents decide whether this API is relevant to a task.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">/llms.txt</span>
                        <span class="inline-flex items-center text-sm font-medium text-cyan-600 dark:text-cyan-400 group-hover:gap-2 gap-1 transition-all">
                            View file
                            <svg aria-hidden="true" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- llms-full.txt --}}
                <a href="/llms-full.txt" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-950/50 dark:to-green-950/50 border border-emerald-200 dark:border-emerald-800/50 p-6 hover:border-emerald-400 dark:hover:border-emerald-600 transition-colors">
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-xs font-mono font-medium mb-4">llms-full.txt</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Complete reference</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Self-contained docs with full API details. No link-following needed for agents to start working.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">/llms-full.txt</span>
                        <span class="inline-flex items-center text-sm font-medium text-emerald-600 dark:text-emerald-400 group-hover:gap-2 gap-1 transition-all">
                            View file
                            <svg aria-hidden="true" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- agents.json --}}
                <a href="/.well-known/agents.json" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-950/50 dark:to-cyan-950/50 border border-teal-200 dark:border-teal-800/50 p-6 hover:border-teal-400 dark:hover:border-teal-600 transition-colors">
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-teal-100 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300 text-xs font-mono font-medium mb-4">agents.json</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Agent workflows</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Pre-defined multi-step workflows for common tasks. Agents can execute sequences without custom logic.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">/.well-known/agents.json</span>
                        <span class="inline-flex items-center text-sm font-medium text-teal-600 dark:text-teal-400 group-hover:gap-2 gap-1 transition-all">
                            View file
                            <svg aria-hidden="true" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- openapi.json --}}
                <a href="/api/openapi.json" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-950/50 dark:to-blue-950/50 border border-sky-200 dark:border-sky-800/50 p-6 hover:border-sky-400 dark:hover:border-sky-600 transition-colors">
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-sky-100 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 text-xs font-mono font-medium mb-4">openapi.json</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">OpenAPI 3.0 spec</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Machine-readable specification for auto-generating client libraries and tool definitions.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">/api/openapi.json</span>
                        <span class="inline-flex items-center text-sm font-medium text-sky-600 dark:text-sky-400 group-hover:gap-2 gap-1 transition-all">
                            View file
                            <svg aria-hidden="true" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Section 3: Built for every kind of integration --}}
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every kind of integration
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whatever you're building, the API has you covered.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- AI Assistants --}}
                <x-sub-audience-card
                    name="AI Assistants"
                    description="Give your AI assistant the ability to create and manage events for users through natural conversation."
                    icon-color="cyan"
                    blog-slug="for-ai-assistants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                {{-- Developer Tools & Scripts --}}
                <x-sub-audience-card
                    name="Developer Tools & Scripts"
                    description="Automate event management with scripts, CLI tools, and custom integrations using the REST API."
                    icon-color="teal"
                    blog-slug="for-developer-tools"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                {{-- Community Bots --}}
                <x-sub-audience-card
                    name="Community Bots"
                    description="Build Discord, Slack, or Telegram bots that create events and notify community members automatically."
                    icon-color="emerald"
                    blog-slug="for-community-bots"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                {{-- Booking Platforms --}}
                <x-sub-audience-card
                    name="Booking Platforms"
                    description="Integrate event creation and ticket management into your existing booking or reservation system."
                    icon-color="sky"
                    blog-slug="for-booking-platforms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                {{-- Calendar Aggregators --}}
                <x-sub-audience-card
                    name="Calendar Aggregators"
                    description="Pull events from Event Schedule into aggregation services and cross-platform calendar views."
                    icon-color="blue"
                    blog-slug="for-calendar-aggregators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                {{-- Custom Integrations --}}
                <x-sub-audience-card
                    name="Custom Integrations"
                    description="Use the OpenAPI spec to generate client libraries and build custom integrations in any language."
                    icon-color="amber"
                    blog-slug="for-custom-integrations"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    {{-- Section 4: Quick Start --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Three steps to your first API call
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-600 to-emerald-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-cyan-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Get your API key</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        <a href="{{ route('sign_up') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-medium">Sign up for free</a> and generate an API key from your account settings. Pro plan required for write operations.
                    </p>
                    <div class="mt-4 bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 text-left">
                        <div class="text-xs font-mono text-gray-500 dark:text-gray-400 leading-relaxed truncate">Authorization: Bearer YOUR_API_KEY</div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-600 to-emerald-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-cyan-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create a schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        POST to /api/schedules with a name and subdomain. Your schedule is live instantly.
                    </p>
                    <div class="mt-4 bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 text-left">
                        <div class="text-xs font-mono text-gray-500 dark:text-gray-400 leading-relaxed">curl -X POST /api/schedules \<br>&nbsp;&nbsp;-d '{"name": "My Schedule"}'</div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-600 to-emerald-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-cyan-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Start managing events</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Create events, set up tickets, and manage your schedule programmatically. Check the API docs for the full reference.
                    </p>
                    <div class="mt-4 bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 text-left">
                        <div class="text-xs font-mono text-gray-500 dark:text-gray-400 leading-relaxed">curl -X POST /api/events \<br>&nbsp;&nbsp;-d '{"name": "AI Meetup"}'</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Key features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="AI-Powered Import"
                    description="Paste text or images to create events instantly"
                    :url="marketing_url('/features/ai')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Analytics"
                    description="Track page views, devices, and traffic sources"
                    :url="marketing_url('/features/analytics')"
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Embed Calendar"
                    description="Add your schedule to any website with one snippet"
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
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything you need to know about the API and LLM discovery files.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What is llms.txt?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            llms.txt is an emerging standard that helps AI models discover and understand your platform. Event Schedule provides both <a href="/llms.txt" class="text-cyan-600 dark:text-cyan-400 hover:underline font-medium">llms.txt</a> (a concise summary for initial discovery and routing) and <a href="/llms-full.txt" class="text-cyan-600 dark:text-cyan-400 hover:underline font-medium">llms-full.txt</a> (a complete, self-contained reference so agents can work without following additional links).
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What can I do with the API?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            You can create, read, update, and delete schedules, events, tickets, sub-schedules, and sales. The API supports smart event creation with venue auto-resolution, member matching, recurring event patterns, and auto-translation to 11 languages.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is the API free to use?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Read operations are free on any Pro schedule. Write operations (creating and updating events, tickets, etc.) require a Pro plan. Ticket sales have zero platform fees - you keep 100% of revenue minus payment processing.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How does authentication work?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Authentication uses API keys passed via the <code class="text-sm bg-gray-100 dark:bg-white/10 px-1.5 py-0.5 rounded">X-API-Key</code> header. Generate your API key from your account settings after signing up. Read-only endpoints on public schedules don't require authentication.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 5: CTA --}}
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-cyan-200 dark:border-cyan-900/20">
        {{-- Mesh gradient background --}}
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-cyan-600/15 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-emerald-600/15 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Give your agent the power of scheduling.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Full REST API. OpenAPI spec. Zero platform fees.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('sign_up') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-cyan-600 to-emerald-600 rounded-2xl overflow-hidden hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-cyan-500/20">
                    <span class="absolute inset-0 animate-shimmer"></span>
                    <span class="relative z-10 flex items-center gap-2">
                        Get Started Free
                        <svg aria-hidden="true" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
                <a href="{{ route('marketing.docs.developer.api') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-700 dark:text-gray-200 border-2 border-gray-300 dark:border-white/20 rounded-2xl hover:border-cyan-500 dark:hover:border-cyan-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all">
                    Read API Docs
                </a>
            </div>
            <p class="mt-6 text-gray-500 text-sm">Pro plan required for write operations</p>
        </div>
    </section>

</x-marketing-layout>
