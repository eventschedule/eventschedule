<x-marketing-layout>
    <x-slot name="title">Free API for AI Agents & Developers - Event Schedule</x-slot>
    <x-slot name="description">Full REST API with OpenAPI spec, llms.txt, and agents.json for AI agents. Create events, manage tickets, and automate scheduling. Zero fees.</x-slot>
    <x-slot name="breadcrumbTitle">For AI Agents</x-slot>

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
        "keywords": "event API, scheduling API, AI agent event management, event automation API, REST API event scheduling",
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

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* For-ai-agents "The Console" styles. The shared es-* motion system lives
           in marketing.css; this holds the agent glow gradient, the drifting API
           console card, and the streaming data-packet motif. */
        .text-gradient-agent {
            background: linear-gradient(135deg, #06b6d4, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(6, 182, 212, 0.3);
        }
        .dark .text-gradient-agent {
            background: linear-gradient(135deg, #22d3ee, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(34, 211, 238, 0.3);
        }
        @keyframes es-agent-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-agent-float { animation: es-agent-float 6s ease-in-out infinite; }

        /* Data-packet stream: request packets flow through the pipe in a wave,
           like API calls streaming between agent and server. */
        .es-flow { display: flex; align-items: center; }
        .es-packet {
            flex: 0 0 auto;
            width: 10px; height: 10px; border-radius: 2px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.6), rgba(16, 185, 129, 0.6));
            animation: es-packet var(--pk-dur, 2.4s) ease-in-out infinite;
            animation-delay: var(--pk-delay, 0s);
        }
        @keyframes es-packet {
            0%, 100% { opacity: 0.15; transform: translateX(-2px) scale(0.8); }
            50% { opacity: 0.95; transform: translateX(2px) scale(1.1); box-shadow: 0 0 8px rgba(6, 182, 212, 0.6); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-agent-float, .es-packet { animation: none !important; }
            .es-packet { opacity: 0.5; transform: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: schedule events, programmatically                  -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(6, 182, 212, 0.3), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(16, 185, 129, 0.28), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.14), rgba(20, 184, 166, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Data-packet stream along the bottom edge -->
            <div class="es-flow absolute bottom-0 left-0 right-0 hidden h-20 items-center justify-center gap-2.5 px-8 pb-6 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                @for ($i = 0; $i < 26; $i++)
                    @php $dur = 2.2 + ($i % 5) * 0.26; $delay = ($i % 12) * 0.12; @endphp
                    <span class="es-packet" style="--pk-dur: {{ $dur }}s; --pk-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For AI Agents & Developers</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Schedule events.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-agent">Programmatically.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                A full REST API with OpenAPI spec. Let your AI agent create events, manage tickets, and automate scheduling with simple HTTP requests.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                llms.txt, agents.json, and a machine-readable OpenAPI spec, so agents can discover, understand, and drive Event Schedule without human help.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('marketing.docs.developer.api') }}" class="group pointer-events-auto relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-emerald-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                    <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                    <span class="relative z-10 flex items-center gap-2">
                        Read API Docs
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
                <a href="/api/openapi.json" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    OpenAPI Spec
                </a>
            </div>

            <!-- Tech-tag marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['OpenAI', 'Claude', 'LangChain', 'Custom Agents', 'REST', 'MCP', 'Python', 'cURL'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100/80 px-4 py-1.5 font-mono text-xs font-semibold text-cyan-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-emerald-400"></span>
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Stats                                                     -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-cyan-500 dark:text-cyan-400">40+ hours</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">to build event management from scratch</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-emerald-500 dark:text-emerald-400">0%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on ticket sales</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-sky-500 dark:text-sky-400">&lt; 5 min</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">to your first API call</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything your <span class="text-gradient-agent">agent needs</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A complete toolkit for programmatic event management.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Full REST API (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                    REST API
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Full REST API</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Create, read, update, and delete schedules, events, tickets, and sub-schedules. Standard HTTP methods, JSON responses, and predictable URL structure. Webhooks for real-time event notifications.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">JSON responses</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">API key auth</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Webhooks</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Rate limiting</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="mb-2 font-mono text-xs text-gray-400">POST /api/events</div>
                                        <div class="font-mono text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                            {<br>
                                            &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"name"</span>: <span class="text-emerald-600 dark:text-emerald-400">"AI Meetup"</span>,<br>
                                            &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"starts_at"</span>: <span class="text-emerald-600 dark:text-emerald-400">"2026-06-15"</span>,<br>
                                            &nbsp;&nbsp;<span class="text-cyan-600 dark:text-cyan-400">"venue"</span>: <span class="text-emerald-600 dark:text-emerald-400">"Tech Hub SF"</span><br>
                                            }
                                        </div>
                                    </div>
                                    <div class="my-2 flex justify-center">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                    </div>
                                    <div class="max-w-xs rounded-2xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="mb-2 font-mono text-xs text-emerald-500">201 Created</div>
                                        <div class="font-mono text-sm leading-relaxed text-gray-600 dark:text-gray-300">
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
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Smart event creation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            Smart Creation
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Smart event creation</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Send event details and the API auto-resolves venues, members, and categories. No need to look up IDs first.</p>
                        <div class="mt-auto space-y-3">
                            <div class="es-ai-field flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300" style="--i: 0;">
                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Venue auto-resolution
                            </div>
                            <div class="es-ai-field flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300" style="--i: 1;">
                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Member matching
                            </div>
                            <div class="es-ai-field flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300" style="--i: 2;">
                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Sub-schedule assignment
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One link for everything -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Shareable URL
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for everything</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Every schedule gets a public URL your agent can share. Events, tickets, and iCal feeds all accessible from one link.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f18]" aria-hidden="true">
                            <div class="truncate font-mono text-sm text-sky-600 dark:text-sky-300">your-schedule.eventschedule.com</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticket management (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Ticket management</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Create ticket types, set prices, and track sales through the API. Zero platform fees on all ticket sales.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Multiple ticket types</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Zero platform fees</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Sales tracking</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="max-w-xs space-y-3">
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" style="--i: 0;">
                                        <div><div class="text-sm font-semibold text-gray-900 dark:text-white">General Admission</div><div class="text-xs text-gray-500 dark:text-gray-400">50 available</div></div>
                                        <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$25</div>
                                    </div>
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" style="--i: 1;">
                                        <div><div class="text-sm font-semibold text-gray-900 dark:text-white">VIP Access</div><div class="text-xs text-gray-500 dark:text-gray-400">10 available</div></div>
                                        <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$75</div>
                                    </div>
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" style="--i: 2;">
                                        <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Student</div><div class="text-xs text-gray-500 dark:text-gray-400">25 available</div></div>
                                        <div class="text-lg font-bold text-amber-600 dark:text-amber-400">$15</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring events -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Recurring events</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set up repeating events with flexible patterns. Daily, weekly, monthly, or custom recurrence rules.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">Every Monday</span>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">1st of month</span>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">Every 2 weeks</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multi-language -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>
                            i18n
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Multi-language</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Events auto-translate to 11 languages. Reach a global audience without extra work.</p>
                        <div class="mt-auto space-y-2">
                            <div class="es-ai-field flex items-center gap-3" style="--i: 0;"><span class="w-8 font-mono text-sm text-teal-600 dark:text-teal-400">EN</span><span class="text-sm text-gray-600 dark:text-gray-300">AI Developer Meetup</span></div>
                            <div class="es-ai-field flex items-center gap-3" style="--i: 1;"><span class="w-8 font-mono text-sm text-teal-600 dark:text-teal-400">ES</span><span class="text-sm text-gray-600 dark:text-gray-300">Encuentro de desarrolladores IA</span></div>
                            <div class="es-ai-field flex items-center gap-3" style="--i: 2;"><span class="w-8 font-mono text-sm text-teal-600 dark:text-teal-400">DE</span><span class="text-sm text-gray-600 dark:text-gray-300">KI-Entwicklertreffen</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- OpenAPI spec -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            OpenAPI 3.0
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">OpenAPI spec</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Download the OpenAPI 3.0 spec to auto-generate client libraries in any language.</p>
                        <div class="mt-auto">
                            <div class="mb-4 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f18]">
                                <div class="truncate font-mono text-sm text-cyan-600 dark:text-cyan-300">/api/openapi.json</div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300">Python</span>
                                <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300">JavaScript</span>
                                <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300">Go</span>
                                <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300">PHP</span>
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
    <!-- 4. AI-native discovery files                                -->
    <!-- ============================================================ -->
    @php
        $discovery = [
            ['llms.txt', '/llms.txt', 'TXT', 'cyan', 'Concise summary for initial discovery and routing. Helps agents decide whether this API is relevant to a task before fetching full documentation.', '/llms.txt',
                '<div class="text-cyan-600 dark:text-cyan-400"># Event Schedule</div><div class="text-gray-500 dark:text-gray-400">&gt; Open-source platform for...</div><div class="text-cyan-600 dark:text-cyan-400 mt-1">## API</div><div class="text-gray-600 dark:text-gray-300">- Authentication: X-API-Key</div>'],
            ['llms-full.txt', '/llms-full.txt', 'TXT', 'emerald', 'Self-contained docs with full API details. No link-following needed for agents to start working - everything an LLM needs is in one file.', '/llms-full.txt',
                '<div class="text-emerald-600 dark:text-emerald-400"># Event Schedule - Full Docs</div><div class="text-gray-500 dark:text-gray-400">&gt; Complete API reference...</div><div class="text-emerald-600 dark:text-emerald-400 mt-1">## Schedule Types</div><div class="text-gray-600 dark:text-gray-300">- Venue | Talent | Curator</div>'],
            ['agents.json', '/.well-known/agents.json', 'JSON', 'teal', 'Pre-defined multi-step workflows for common tasks. Agents can execute complete sequences like registration and event creation without custom logic.', '/.well-known/agents.json',
                '<div class="text-gray-600 dark:text-gray-300">{</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;<span class="text-teal-600 dark:text-teal-400">"name"</span>: <span class="text-amber-600 dark:text-amber-400">"Event Schedule API"</span>,</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;<span class="text-teal-600 dark:text-teal-400">"flows"</span>: [</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;&nbsp;&nbsp;{ <span class="text-teal-600 dark:text-teal-400">"name"</span>: <span class="text-amber-600 dark:text-amber-400">"register_and_setup"</span> }</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;]</div><div class="text-gray-600 dark:text-gray-300">}</div>'],
            ['openapi.json', '/api/openapi.json', 'JSON', 'sky', 'Machine-readable OpenAPI 3.0 specification for auto-generating client libraries and tool definitions in any language.', '/api/openapi.json',
                '<div class="text-gray-600 dark:text-gray-300">{</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;<span class="text-sky-600 dark:text-sky-400">"openapi"</span>: <span class="text-amber-600 dark:text-amber-400">"3.0.3"</span>,</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;<span class="text-sky-600 dark:text-sky-400">"info"</span>: {</div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-sky-600 dark:text-sky-400">"title"</span>: <span class="text-amber-600 dark:text-amber-400">"Event Schedule API"</span></div><div class="text-gray-600 dark:text-gray-300">&nbsp;&nbsp;}</div><div class="text-gray-600 dark:text-gray-300">}</div>'],
        ];
        $discoveryStyles = [
            'cyan' => ['border-cyan-200 dark:border-cyan-800/50 hover:border-cyan-400 dark:hover:border-cyan-500 hover:shadow-cyan-500/10', 'from-cyan-50 to-teal-50 dark:from-cyan-950/40 dark:to-teal-950/40', 'bg-cyan-100 dark:bg-cyan-500/15 text-cyan-700 dark:text-cyan-300', 'text-cyan-600 dark:text-cyan-400'],
            'emerald' => ['border-emerald-200 dark:border-emerald-800/50 hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-emerald-500/10', 'from-emerald-50 to-green-50 dark:from-emerald-950/40 dark:to-green-950/40', 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300', 'text-emerald-600 dark:text-emerald-400'],
            'teal' => ['border-teal-200 dark:border-teal-800/50 hover:border-teal-400 dark:hover:border-teal-500 hover:shadow-teal-500/10', 'from-teal-50 to-cyan-50 dark:from-teal-950/40 dark:to-cyan-950/40', 'bg-teal-100 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300', 'text-teal-600 dark:text-teal-400'],
            'sky' => ['border-sky-200 dark:border-sky-800/50 hover:border-sky-400 dark:hover:border-sky-500 hover:shadow-sky-500/10', 'from-sky-50 to-blue-50 dark:from-sky-950/40 dark:to-blue-950/40', 'bg-sky-100 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300', 'text-sky-600 dark:text-sky-400'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    <span class="text-gradient-agent">AI-native</span> discovery
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Standard discovery files so AI agents can find and understand the API without human help.
                </p>
            </div>

            <div class="space-y-6" data-reveal-group="90">
                @foreach ($discovery as [$name, $href, $badge, $color, $desc, $path, $preview])
                    @php [$cardCls, $panelCls, $badgeCls, $linkCls] = $discoveryStyles[$color]; @endphp
                    <a href="{{ $href }}" data-reveal class="group flex flex-col overflow-hidden rounded-2xl border bg-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:bg-[#0a0a0f] md:flex-row {{ $cardCls }}">
                        <div class="flex-shrink-0 bg-gradient-to-br p-5 md:w-[280px] lg:w-[320px] {{ $panelCls }}">
                            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-green-400"></span>
                                </div>
                                <div class="p-3 font-mono text-xs leading-relaxed">{!! $preview !!}</div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col justify-center p-6">
                            <div class="mb-2 flex items-center gap-3">
                                <h3 class="font-mono text-xl font-bold text-gray-900 dark:text-white">{{ $name }}</h3>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeCls }}">{{ $badge }}</span>
                            </div>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $path }}</span>
                                <span class="inline-flex items-center gap-1 text-sm font-medium transition-all group-hover:gap-2 {{ $linkCls }}">
                                    View file
                                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Quick start (dark terminal band)                          -->
    <!-- ============================================================ -->
    @php
        $quickstart = [
            ['1', 'Get your API key', '<a href="'.app_url('/sign_up').'" class="font-medium text-cyan-300 hover:underline">Sign up for free</a> and generate an API key from your account settings.', 'Authorization: Bearer YOUR_API_KEY'],
            ['2', 'Create a schedule', 'POST to /api/schedules with a name and subdomain. Your schedule is live instantly.', 'curl -X POST /api/schedules \\<br>&nbsp;&nbsp;-d \'{"name": "My Schedule"}\''],
            ['3', 'Start managing events', 'Create events, set up tickets, and manage your schedule programmatically.', 'curl -X POST /api/events \\<br>&nbsp;&nbsp;-d \'{"name": "AI Meetup"}\''],
        ];
    @endphp
    <section id="quickstart" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-flow absolute bottom-0 left-0 right-0 flex h-16 items-center justify-center gap-2.5 px-8 pb-3 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                    @for ($i = 0; $i < 26; $i++)
                        @php $dur = 2.2 + ($i % 5) * 0.26; $delay = ($i % 12) * 0.12; @endphp
                        <span class="es-packet" style="--pk-dur: {{ $dur }}s; --pk-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Three steps to your <span class="text-gradient-agent">first API call</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($quickstart as [$num, $title, $desc, $code])
                        <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-emerald-600 text-xl font-bold text-white shadow-lg shadow-cyan-600/25">
                                {{ $num }}
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="flex-1 text-sm text-gray-400">{!! $desc !!}</p>
                            <div class="mt-4 rounded-xl border border-white/10 bg-black/30 p-3 text-left">
                                <div class="truncate font-mono text-xs leading-relaxed text-gray-300">{!! $code !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Built for every kind of integration (sub-audience cards) -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for every kind of <span class="text-gradient-agent">integration</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whatever you're building, the API has you covered.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- AI Assistants -->
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

                <!-- Developer Tools & Scripts -->
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

                <!-- Community Bots -->
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

                <!-- Booking Platforms -->
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

                <!-- Calendar Aggregators -->
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

                <!-- Custom Integrations -->
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

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="AI Features" description="Import, generate content, create brand style, and more with AI" :url="marketing_url('/features/ai')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Add your schedule to any website with one snippet" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-webinars', 'Webinars'], ['/for-virtual-conferences', 'Virtual Conferences'], ['/for-curators', 'Curators'], ['/for-online-classes', 'Online Classes']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
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
    @php
        $faqLink = 'font-medium text-cyan-600 hover:underline dark:text-cyan-400';
        $faqs = [
            ['What is llms.txt?', 'llms.txt is an emerging standard that helps AI models discover and understand your platform. Event Schedule provides both <a href="/llms.txt" class="'.$faqLink.'">llms.txt</a> (a concise summary for initial discovery and routing) and <a href="/llms-full.txt" class="'.$faqLink.'">llms-full.txt</a> (a complete, self-contained reference so agents can work without following additional links).'],
            ['What can I do with the API?', 'You can create, read, update, and delete schedules, events, tickets, sub-schedules, and sales. The API supports smart event creation with venue auto-resolution, member matching, recurring event patterns, and auto-translation to 11 languages.'],
            ['Is the API free to use?', 'Read operations are free on any Pro schedule. Write operations (creating and updating events, tickets, etc.) require a Pro plan. Ticket sales have zero platform fees - you keep 100% of revenue minus payment processing.'],
            ['How does authentication work?', 'Authentication uses API keys passed via the <code class="rounded bg-gray-100 px-1.5 py-0.5 text-sm dark:bg-white/10">X-API-Key</code> header. Generate your API key from your account settings after signing up. Read-only endpoints on public schedules don\'t require authentication.'],
        ];
    @endphp
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-agent">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about the API and LLM discovery files.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{!! $a !!}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(6, 182, 212, 0.3), rgba(6, 182, 212, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-flow absolute bottom-0 left-0 right-0 flex h-14 items-center justify-center gap-2.5 px-8 pb-3 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                        @for ($i = 0; $i < 22; $i++)
                            @php $dur = 2.2 + ($i % 5) * 0.26; $delay = ($i % 12) * 0.12; @endphp
                            <span class="es-packet" style="--pk-dur: {{ $dur }}s; --pk-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Give your agent the <span class="text-gradient-agent">power of scheduling.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Full REST API. OpenAPI spec. Zero platform fees.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-agent" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-emerald-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-300">
                        or <a href="{{ route('marketing.docs.developer.api') }}" class="font-medium text-cyan-300 hover:underline">read the API docs</a>
                    </p>
                    <p class="mt-2 text-sm text-gray-400">Pro plan required for write operations</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
