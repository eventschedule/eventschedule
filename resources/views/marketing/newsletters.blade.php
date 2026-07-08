<x-marketing-layout>
    <x-slot name="title">Newsletter Builder for Events - Event Schedule</x-slot>
    <x-slot name="description">Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, professional templates, audience segments, and delivery analytics.</x-slot>
    <x-slot name="breadcrumbTitle">Newsletters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Newsletters",
        "description": "Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, professional templates, audience segments, A/B testing, and delivery analytics.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Email Marketing"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do subscribers join my newsletter?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Visitors can follow your schedule directly from your public schedule page. You can also target ticket buyers and manually add email addresses. All subscribers can unsubscribe with one click."
                }
            },
            {
                "@type": "Question",
                "name": "How many newsletters can I send?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The free plan includes 10 newsletter emails per month per schedule. The Pro plan increases this to 100 newsletter emails per month, giving you plenty of capacity for regular audience communication."
                }
            },
            {
                "@type": "Question",
                "name": "How does email deliverability work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule handles email delivery infrastructure for you with proper authentication headers. Pro users can also configure custom SMTP servers per schedule for maximum deliverability and branding control."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Newsletters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Email Marketing Software",
        "operatingSystem": "Web",
        "description": "Send branded newsletters to followers and ticket buyers with a drag-and-drop editor, professional templates, audience segments, A/B testing, and real-time delivery analytics.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Drag-and-drop block editor",
            "5 professional templates",
            "Audience segments",
            "A/B testing",
            "Real-time delivery analytics",
            "Open and click tracking"
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
        /* For newsletters "The Send" styles. The shared es-* motion system lives in
           marketing.css; this holds the sky/cyan glow gradient, the drifting newsletter
           preview card, and the send-stream motif (emails flowing out to inboxes). */
        .text-gradient-news {
            background: linear-gradient(135deg, #0284c7, #0891b2, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(2, 132, 199, 0.3);
        }
        .dark .text-gradient-news {
            background: linear-gradient(135deg, #38bdf8, #22d3ee, #7dd3fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(56, 189, 248, 0.3);
        }
        @keyframes es-news-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-news-float { animation: es-news-float 6s ease-in-out infinite; }

        /* Send-stream motif: dashes flow rightward and brighten, like newsletters
           traveling out to subscriber inboxes. */
        .es-stream { display: flex; align-items: center; }
        .es-dash {
            flex: 0 0 auto; height: 3px; border-radius: 9999px;
            background: linear-gradient(to right, rgba(56, 189, 248, 0), rgba(56, 189, 248, 0.9));
            animation: es-dash-flow var(--ds-dur, 2.4s) ease-in-out infinite;
            animation-delay: var(--ds-delay, 0s);
        }
        @keyframes es-dash-flow {
            0%, 100% { opacity: 0.2; transform: translateX(-7px); }
            50% { opacity: 1; transform: translateX(7px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-news-float, .es-dash, .animate-pulse-slow { animation: none !important; }
            .es-dash { opacity: 0.55; transform: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: newsletter builder                                  -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.28), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.14), rgba(56, 189, 248, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Send stream along the bottom edge -->
            <div class="es-stream absolute bottom-10 left-0 right-0 mx-auto hidden h-16 max-w-4xl flex-col items-stretch justify-center gap-3 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($row = 0; $row < 3; $row++)
                    <div class="flex items-center gap-3">
                        @for ($i = 0; $i < 22; $i++)
                            @php $w = [24, 40, 32, 16, 48][$i % 5]; $dur = 2.0 + (($i + $row) % 5) * 0.3; $delay = (($i * 2 + $row) % 13) * 0.14; @endphp
                            <span class="es-dash" style="width: {{ $w }}px; --ds-dur: {{ $dur }}s; --ds-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Newsletters</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Newsletter</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-news">builder</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Send branded newsletters to your followers and ticket buyers with a drag-and-drop editor, professional templates, audience segments, A/B testing, and delivery analytics.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.newsletters') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Newsletters guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Everything you need to <span class="text-gradient-news">send great emails</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Drag-and-drop builder (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Visual Editor
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Build with blocks</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Compose newsletters visually with a variety of block types: headings, rich text with markdown, images, buttons, event listings, sponsors, polls, social links, dividers, spacers, profile images, and header banners. Drag to reorder, clone, or delete blocks.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">15 block types</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Drag-and-drop</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Markdown support</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-56" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="space-y-2">
                                        <div class="es-ai-field rounded-lg border border-gray-200 bg-white p-2.5 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                            <div class="mb-1 text-[10px] text-sky-500 dark:text-sky-400">Heading</div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">This Week in Events</div>
                                        </div>
                                        <div class="es-ai-field rounded-lg border border-gray-200 bg-white p-2.5 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                            <div class="mb-1 text-[10px] text-sky-500 dark:text-sky-400">Text</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Check out our upcoming shows...</div>
                                        </div>
                                        <div class="es-ai-field rounded-lg border border-sky-200 bg-sky-50 p-2.5 dark:border-sky-400/30 dark:bg-sky-500/10" style="--i: 2;">
                                            <div class="mb-1 text-[10px] text-sky-500 dark:text-sky-400">Events</div>
                                            <div class="text-xs text-gray-900 dark:text-white">3 events selected</div>
                                        </div>
                                        <div class="es-ai-field rounded-lg border border-gray-200 bg-white p-2.5 text-center dark:border-white/10 dark:bg-white/10" style="--i: 3;">
                                            <div class="inline-flex rounded-full bg-sky-500 px-4 py-1 text-xs text-white">View All Events</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Professional templates -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            5 Templates
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Start polished</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Choose from Modern, Classic, Minimal, Bold, or Compact. Each template has its own typography, color palette, button style, and event layout.</p>
                        <div class="mt-auto flex justify-center gap-2" aria-hidden="true">
                            <div class="h-14 w-10 rounded-lg border-2 border-sky-300 bg-sky-500 dark:border-sky-400" title="Modern"></div>
                            <div class="h-14 w-10 rounded-lg border-2 border-amber-500 bg-amber-700 dark:border-amber-400" title="Classic"></div>
                            <div class="h-14 w-10 rounded-lg border-2 border-gray-300 bg-gray-400 dark:border-gray-500" title="Minimal"></div>
                            <div class="h-14 w-10 rounded-lg border-2 border-red-500 bg-red-700 dark:border-red-400" title="Bold"></div>
                            <div class="h-14 w-10 rounded-lg border-2 border-emerald-400 bg-emerald-600" title="Compact"></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Audience segments -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Smart Targeting
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Reach the right people</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Target all followers, ticket buyers (filter by event or date range), manual email lists, or event group members. Combine segments and auto-deduplicate.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-100 p-2 dark:border-sky-400/30 dark:bg-sky-500/20" style="--i: 0;">
                                <span class="h-3 w-3 rounded-full bg-sky-500"></span>
                                <span class="text-sm text-gray-900 dark:text-white">All Followers</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <span class="h-3 w-3 rounded-full bg-cyan-500"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Ticket Buyers</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 2;">
                                <span class="h-3 w-3 rounded-full bg-teal-500"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Manual List</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 3;">
                                <span class="h-3 w-3 rounded-full bg-sky-500"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Group Members</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- A/B testing (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Optimize
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Test and learn</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Split-test subject lines or content across a sample of your audience (5-50%). Pick the winner by open rate or click rate. Set wait time from 1 to 72 hours. The winning variant is automatically sent to the remaining recipients.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: 0;">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Variant A</div>
                                    <div class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">24%</div>
                                    <div class="text-sm text-sky-600 dark:text-sky-400">Open rate</div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                        <div class="h-full rounded-full bg-sky-400" style="width: 24%"></div>
                                    </div>
                                </div>
                                <div class="es-ai-field rounded-xl border border-sky-200 bg-sky-50 p-4 text-center dark:border-sky-400/30 dark:bg-sky-500/10" style="--i: 1;">
                                    <div class="mb-2 text-xs text-sky-600 dark:text-sky-300">Variant B</div>
                                    <div class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">31%</div>
                                    <div class="text-sm text-sky-600 dark:text-sky-400">Open rate</div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                        <div class="h-full rounded-full bg-sky-500" style="width: 31%"></div>
                                    </div>
                                    <div class="mt-2 inline-flex items-center gap-1 text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                        <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Winner
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Delivery analytics (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>
                                    Real-time Stats
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Track every send</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Monitor opens, clicks, and failures in real time. View opens-over-time and clicks-over-time charts, top clicked links, and per-recipient engagement details. A/B test results shown side-by-side.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Open tracking</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Click tracking</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Top links</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 grid grid-cols-3 gap-2">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">1,248</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Sent</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-sky-600 dark:text-sky-400">42%</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Opens</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">18%</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Clicks</div>
                                        </div>
                                    </div>
                                    <div class="flex h-16 items-end justify-between gap-1">
                                        @foreach ([20, 35, 65, 90, 100, 95, 85, 80] as $bi => $bh)
                                            <div class="w-4 rounded-t bg-sky-500" style="height: {{ $bh }}%; opacity: {{ 0.3 + $bi * 0.07 }};"></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-1 text-center text-[10px] text-gray-500 dark:text-gray-400">Opens over time</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Customize everything -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Full Control
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Customize everything</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set background, accent, and text colors. Choose from 5 font families. Pick rounded or square buttons. Schedule sends for later. Send test emails first. Custom SMTP per schedule. One-click unsubscribe with compliance headers.</p>
                        <div class="mt-auto flex items-center justify-center gap-4" aria-hidden="true">
                            <div class="flex gap-2">
                                <div class="h-8 w-8 rounded-full border-2 border-white bg-sky-500 shadow dark:border-white/20"></div>
                                <div class="h-8 w-8 rounded-full border-2 border-white bg-cyan-500 shadow dark:border-white/20"></div>
                                <div class="h-8 w-8 rounded-full border-2 border-white bg-gray-800 shadow dark:border-white/20 dark:bg-gray-200"></div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-100 p-2 dark:border-white/10 dark:bg-white/10">
                                <svg aria-hidden="true" class="h-6 w-6 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
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
    <!-- 3. Keep exploring (dark band)                               -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(2, 132, 199, 0.24), rgba(2, 132, 199, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-stream absolute bottom-8 left-0 right-0 mx-auto flex h-14 flex-col items-stretch justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($row = 0; $row < 2; $row++)
                        <div class="flex items-center justify-center gap-3">
                            @for ($i = 0; $i < 26; $i++)
                                @php $w = [24, 40, 32, 16, 48][$i % 5]; $dur = 2.0 + (($i + $row) % 5) * 0.3; $delay = (($i * 2 + $row) % 13) * 0.14; @endphp
                                <span class="es-dash" style="width: {{ $w }}px; --ds-dur: {{ $dur }}s; --ds-delay: {{ $delay }}s;"></span>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Keep <span class="text-gradient-news">exploring</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Learn the ropes and see what pairs well with newsletters.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                    <!-- Read the guide -->
                    <a href="{{ route('marketing.docs.newsletters') }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8 transition-all duration-300 hover:-translate-y-1 hover:border-sky-500/30 hover:bg-white/[0.06]">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-sky-300">Read the guide</h3>
                        <p class="mb-4 text-lg text-gray-300">Learn how to get the most out of newsletters.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-400 transition-all group-hover:gap-3">
                            Read guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- Next feature: Calendar Sync -->
                    <a href="{{ marketing_url('/features/calendar-sync') }}" data-reveal class="group flex flex-col rounded-2xl border border-sky-500/20 bg-gradient-to-br from-sky-500/10 to-cyan-500/10 p-8 transition-all duration-300 hover:-translate-y-1 hover:border-sky-500/40">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-sky-300">Calendar Sync</h3>
                        <p class="mb-4 text-lg text-gray-300">Two-way sync with Google Calendar. Let attendees add events to Apple, Google, or Outlook calendars.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-400 transition-all group-hover:gap-3">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- Popular with -->
                    <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-white">Popular with</h3>
                        <div class="space-y-3">
                            <a href="{{ marketing_url('/for-musicians') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-sky-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Musicians</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-bars') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-sky-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Bars</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-sky-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Venues</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-news">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about newsletters.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do subscribers join my newsletter?', 'Visitors can follow your schedule directly from your public schedule page. You can also target ticket buyers and manually add email addresses. All subscribers can unsubscribe with one click.'],
                    ['How many newsletters can I send?', 'The free plan includes 10 newsletter emails per month per schedule. The Pro plan increases this to 100 newsletter emails per month, giving you plenty of capacity for regular audience communication.'],
                    ['How does email deliverability work?', 'Event Schedule handles email delivery infrastructure for you with proper authentication headers. Pro users can also configure custom SMTP servers per schedule for maximum deliverability and branding control.'],
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
    <!-- 5. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Fan Videos"
                        description="Let fans share videos and comments on your events"
                        :url="marketing_url('/features/fan-videos')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Recurring Events"
                        description="Set events to repeat on any schedule automatically"
                        :url="marketing_url('/features/recurring-events')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-stream absolute bottom-8 left-0 right-0 mx-auto flex h-14 flex-col items-stretch justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($row = 0; $row < 2; $row++)
                            <div class="flex items-center justify-center gap-3">
                                @for ($i = 0; $i < 20; $i++)
                                    @php $w = [24, 40, 32, 16, 48][$i % 5]; $dur = 2.0 + (($i + $row) % 5) * 0.3; $delay = (($i * 2 + $row) % 13) * 0.14; @endphp
                                    <span class="es-dash" style="width: {{ $w }}px; --ds-dur: {{ $dur }}s; --ds-delay: {{ $delay }}s;"></span>
                                @endfor
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start sending <span class="text-gradient-news">newsletters today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Reach your audience with branded emails. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
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
