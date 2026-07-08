<x-marketing-layout>
    <x-slot name="title">Replace Google Forms, Mailchimp, Canva & More for Events | Event Schedule</x-slot>
    <x-slot name="description">Replace Google Forms, Mailchimp, Canva, Notion, and Trello with Event Schedule: purpose-built event management with ticketing, event pages, and AI.</x-slot>
    <x-slot name="breadcrumbTitle">Replace</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Replace Your Event Tools with Event Schedule",
        "description": "General-purpose tools that Event Schedule can replace for event management.",
        "url": "{{ config('app.url') }}/replace",
        "numberOfItems": 12,
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Google Forms", "url": "{{ config('app.url') }}/google-forms-replacement"},
            {"@type": "ListItem", "position": 2, "name": "Mailchimp", "url": "{{ config('app.url') }}/mailchimp-replacement"},
            {"@type": "ListItem", "position": 3, "name": "Canva", "url": "{{ config('app.url') }}/canva-replacement"},
            {"@type": "ListItem", "position": 4, "name": "Linktree", "url": "{{ config('app.url') }}/linktree-replacement"},
            {"@type": "ListItem", "position": 5, "name": "Google Sheets", "url": "{{ config('app.url') }}/google-sheets-replacement"},
            {"@type": "ListItem", "position": 6, "name": "Calendly", "url": "{{ config('app.url') }}/calendly-replacement"},
            {"@type": "ListItem", "position": 7, "name": "SurveyMonkey", "url": "{{ config('app.url') }}/surveymonkey-replacement"},
            {"@type": "ListItem", "position": 8, "name": "Doodle", "url": "{{ config('app.url') }}/doodle-replacement"},
            {"@type": "ListItem", "position": 9, "name": "QR Code Generators", "url": "{{ config('app.url') }}/qr-code-generator-replacement"},
            {"@type": "ListItem", "position": 10, "name": "Squarespace", "url": "{{ config('app.url') }}/squarespace-replacement"},
            {"@type": "ListItem", "position": 11, "name": "Notion", "url": "{{ config('app.url') }}/notion-replacement"},
            {"@type": "ListItem", "position": 12, "name": "Trello", "url": "{{ config('app.url') }}/trello-replacement"}
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-replace {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-replace {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-replace {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature motif: a row of verdict marks (check vs cross) */
        .es-verdict {
            flex: 0 0 auto;
            animation: es-verdict-pulse var(--vd-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--vd-delay, 0s);
        }
        @keyframes es-verdict-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-verdict, .animate-pulse-slow { animation: none !important; }
            .es-verdict { opacity: 0.55; transform: none; }
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
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(68svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <div class="es-verdicts absolute bottom-6 left-0 right-0 mx-auto hidden h-14 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; $win = $i % 3 !== 2; $sz = [20, 26, 18, 24][$i % 4]; @endphp
                    <span class="es-verdict {{ $win ? 'text-emerald-500' : 'text-gray-400 dark:text-gray-600' }}" style="--vd-dur: {{ $dur }}s; --vd-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            @if ($win)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            @endif
                        </svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Replace your workarounds</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Replace Your Event Tools</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-replace">with Event Schedule</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                You are using 5 tools to do what one platform was built for. Ticketing, calendars, graphics, emails, and event pages - all in one place.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Before / After Consolidation                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-stretch gap-8 md:grid-cols-2" data-reveal-group="90">
                <div data-reveal class="rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center dark:border-white/10 dark:bg-white/5">
                    <div class="mb-6 text-sm font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Before</div>
                    <div class="mb-8 flex flex-wrap justify-center gap-3">
                        @foreach (['Google Forms', 'Mailchimp', 'Canva', 'Calendly', 'Linktree'] as $t)
                            <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-400">{{ $t }}</span>
                        @endforeach
                    </div>
                    <div class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">5 tools</div>
                    <div class="text-lg text-gray-500 dark:text-gray-400">~$50+/mo combined</div>
                </div>

                <div data-reveal class="rounded-2xl border border-blue-200 bg-blue-50/50 p-8 text-center dark:border-blue-500/30 dark:bg-blue-500/5">
                    <div class="mb-6 text-sm font-medium uppercase tracking-wider text-blue-500 dark:text-blue-400">After</div>
                    <div class="mb-8 flex flex-wrap justify-center gap-3">
                        @foreach (['Ticketing', 'Newsletters', 'Graphics', 'Calendar', 'Event Pages'] as $t)
                            <span class="rounded-lg border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/20 dark:text-blue-300">{{ $t }}</span>
                        @endforeach
                    </div>
                    <div class="mb-1 text-3xl font-bold text-gradient-replace">1 platform</div>
                    <div class="text-lg text-gray-500 dark:text-gray-400">From $5/mo</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Why Consolidate                                          -->
    <!-- ============================================================ -->
    <section class="bg-white pb-24 pt-0 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Why consolidate <span class="text-gradient-replace">your tools?</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">One platform instead of five. Here is what you gain.</p>
            </div>

            @php
                $benefits = [
                    ['bg-emerald-100 dark:bg-emerald-500/20', 'text-emerald-600 dark:text-emerald-400', '$50+/mo', 'Save money', 'One plan from $5/month replaces $50+ in separate tools. Zero platform fees on ticket sales, and a free plan that covers most needs.', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['bg-blue-100 dark:bg-blue-500/20', 'text-blue-600 dark:text-blue-400', '30 sec', 'Save time', 'Import events in seconds with AI instead of manual entry. Events, tickets, attendees, and emails all live in one place.', 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['bg-sky-100 dark:bg-sky-500/20', 'text-sky-600 dark:text-sky-400', '0% fees', 'Stay connected', 'Ticketing, newsletters, graphics, and two-way Google Calendar sync in one platform. Ticket buyers become subscribers automatically.', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                @foreach ($benefits as $b)
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-gray-50 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl {{ $b[0] }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $b[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $b[5] }}" />
                            </svg>
                        </div>
                        <div class="mb-2 text-3xl font-bold {{ $b[1] }}">{{ $b[2] }}</div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $b[3] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $b[4] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Tool Cards                                               -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>One platform, <span class="text-gradient-replace">many tools replaced</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Replace Google Forms, Mailchimp, Canva, Calendly, and more with a single event management platform.</p>
            </div>

            @php
                $toolGroups = [
                    [
                        'label' => 'Registration & Forms',
                        'tools' => [
                            ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'desc' => 'Replace manual registration forms with built-in ticketing, payments, and automatic confirmations.', 'pricing' => 'Free (+ add-ons) vs. $5/mo', 'icon' => 'clipboard', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                            ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey', 'desc' => 'Replace repurposed survey forms with purpose-built event registration, ticketing, and attendee management.', 'pricing' => '$25+/mo vs. $5/mo', 'icon' => 'clipboard', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                            ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'desc' => 'Replace date polling with a full event platform. Create, publish, and sell tickets for events in one place.', 'pricing' => '$7+/mo/user vs. $5/mo', 'icon' => 'calendar', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                        ],
                    ],
                    [
                        'label' => 'Marketing & Communication',
                        'tools' => [
                            ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp', 'desc' => 'Replace a separate email tool with built-in newsletters, A/B testing, and integrated attendee data.', 'pricing' => '$13+/mo vs. $5/mo', 'icon' => 'mail', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                            ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'desc' => 'Replace manual flyer design with auto-generated event graphics and AI-powered flyers.', 'pricing' => '$15/mo vs. $5/mo', 'icon' => 'image', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                            ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'desc' => 'Replace a list of links with a schedule page that shows events, sells tickets, and collects subscribers.', 'pricing' => '$9/mo vs. $5/mo', 'icon' => 'link', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                        ],
                    ],
                    [
                        'label' => 'Scheduling & Tracking',
                        'tools' => [
                            ['name' => 'Calendly', 'route' => 'marketing.replace_calendly', 'desc' => 'Replace appointment scheduling with public event pages, ticketing, and a shareable event calendar.', 'pricing' => '$10/mo/user vs. $5/mo', 'icon' => 'calendar', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                            ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'desc' => 'Replace spreadsheet tracking with automatic attendee management, sales dashboards, and CSV exports.', 'pricing' => 'Free (manual effort) vs. $5/mo', 'icon' => 'chart', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                            ['name' => 'QR Code Generators', 'route' => 'marketing.replace_qr_code_generators', 'desc' => 'Replace standalone QR tools with tickets that include built-in QR codes and a live check-in dashboard.', 'pricing' => '$5 to $15/mo vs. $5/mo', 'icon' => 'qr', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                        ],
                    ],
                    [
                        'label' => 'Planning & Websites',
                        'tools' => [
                            ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace', 'desc' => 'Replace a full website builder with ready-made event pages, integrated ticketing, and no plugins needed.', 'pricing' => '$16+/mo vs. $5/mo', 'icon' => 'globe', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                            ['name' => 'Notion', 'route' => 'marketing.replace_notion', 'desc' => 'Replace internal workspace planning with public event pages, built-in ticketing, and automatic calendar sync.', 'pricing' => '$10/mo/user vs. $5/mo', 'icon' => 'code', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                            ['name' => 'Trello', 'route' => 'marketing.replace_trello', 'desc' => 'Replace task boards with a full event platform. Public event pages, ticketing, and payments built in.', 'pricing' => '$6/mo/user vs. $5/mo', 'icon' => 'layout', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                        ],
                    ],
                ];
            @endphp

            <div class="space-y-12">
                @foreach ($toolGroups as $group)
                    <div>
                        <h3 class="mb-4 text-sm font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500" data-reveal>{{ $group['label'] }}</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                            @foreach ($group['tools'] as $tool)
                                <a href="{{ route($tool['route']) }}" data-reveal class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-8 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl {{ $tool['icon_bg'] }}">
                                        <x-marketing-icon :icon="$tool['icon']" :class="'w-5 h-5 ' . $tool['icon_color']" />
                                    </div>
                                    <h3 class="mb-3 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $tool['name'] }}</h3>
                                    <p class="mb-3 flex-grow text-sm text-gray-500 dark:text-gray-400">{{ $tool['desc'] }}</p>
                                    @if (!empty($tool['pricing']))
                                        <p class="mb-4 text-xs font-medium text-gray-400 dark:text-gray-500">{{ $tool['pricing'] }}</p>
                                    @endif
                                    <span class="mt-auto inline-flex items-center text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-400">
                                        Learn more
                                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mx-auto mt-16 max-w-3xl">
                <a href="{{ route('marketing.compare') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-8 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <h2 class="mb-2 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400 md:text-2xl">
                            Looking for direct platform comparisons?
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400">
                            See how Event Schedule compares to Eventbrite, Luma, and Ticket Tailor.
                        </p>
                    </div>
                    <svg aria-hidden="true" class="ml-6 h-6 w-6 shrink-0 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-replace">simplify?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free schedule today. No credit card required, no platform fees ever.
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
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
