<x-marketing-layout>
    <x-slot name="title">Replace Google Forms, Mailchimp, Canva & More for Events | Event Schedule</x-slot>
    <x-slot name="description">Replace Google Forms, Mailchimp, Canva, Notion, Trello, and other general-purpose tools with Event Schedule. Purpose-built event management with ticketing, event pages, and AI features.</x-slot>
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

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Replace your workarounds</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Replace Your Event Tools<br>
                <span class="text-gradient">with Event Schedule</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto animate-reveal delay-200" style="opacity: 0;">
                You are using 5 tools to do what one platform was built for. Ticketing, calendars, graphics, emails, and event pages - all in one place.
            </p>
        </div>
    </section>

    <!-- Before/After Consolidation Visual -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
                <!-- Before: Scattered tools -->
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-8 text-center">
                    <div class="text-sm font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-6">Before</div>
                    <div class="flex flex-wrap justify-center gap-3 mb-8">
                        <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-400">Google Forms</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-400">Mailchimp</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-400">Canva</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-400">Calendly</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-400">Linktree</span>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">5 tools</div>
                    <div class="text-lg text-gray-500 dark:text-gray-400">~$50+/mo combined</div>
                </div>

                <!-- After: Event Schedule -->
                <div class="rounded-2xl border border-blue-200 dark:border-blue-500/30 bg-blue-50/50 dark:bg-blue-500/5 p-8 text-center">
                    <div class="text-sm font-medium text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-6">After</div>
                    <div class="flex flex-wrap justify-center gap-3 mb-8">
                        <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 text-sm font-medium text-blue-700 dark:text-blue-300">Ticketing</span>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 text-sm font-medium text-blue-700 dark:text-blue-300">Newsletters</span>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 text-sm font-medium text-blue-700 dark:text-blue-300">Graphics</span>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 text-sm font-medium text-blue-700 dark:text-blue-300">Calendar</span>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 text-sm font-medium text-blue-700 dark:text-blue-300">Event Pages</span>
                    </div>
                    <div class="text-3xl font-bold text-gradient mb-1">1 platform</div>
                    <div class="text-lg text-gray-500 dark:text-gray-400">From $5/mo</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Consolidate -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 pt-0">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why consolidate your tools?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    One platform instead of five. Here is what you gain.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-8">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">$50+/mo</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Save money</h3>
                    <p class="text-gray-500 dark:text-gray-400">One plan from $5/month replaces $50+ in separate tools. Zero platform fees on ticket sales, and a free plan that covers most needs.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-8">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">30 sec</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Save time</h3>
                    <p class="text-gray-500 dark:text-gray-400">Import events in seconds with AI instead of manual entry. Events, tickets, attendees, and emails all live in one place.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-8">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-sky-600 dark:text-sky-400 mb-2">0% fees</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Stay connected</h3>
                    <p class="text-gray-500 dark:text-gray-400">Ticketing, newsletters, graphics, and two-way Google Calendar sync in one platform. Ticket buyers become subscribers automatically.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Tool Cards -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    One platform, many tools replaced
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Replace Google Forms, Mailchimp, Canva, Calendly, and more with a single event management platform.
                </p>
            </div>

            @php
                $toolGroups = [
                    [
                        'label' => 'Registration & Forms',
                        'tools' => [
                            ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'desc' => 'Replace manual registration forms with built-in ticketing, payments, and automatic confirmations.', 'icon' => 'clipboard', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                            ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey', 'desc' => 'Replace repurposed survey forms with purpose-built event registration, ticketing, and attendee management.', 'icon' => 'clipboard', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                            ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'desc' => 'Replace date polling with a full event platform. Create, publish, and sell tickets for events in one place.', 'icon' => 'calendar', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                        ],
                    ],
                    [
                        'label' => 'Marketing & Communication',
                        'tools' => [
                            ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp', 'desc' => 'Replace a separate email tool with built-in newsletters, A/B testing, and integrated attendee data.', 'icon' => 'mail', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                            ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'desc' => 'Replace manual flyer design with auto-generated event graphics and AI-powered flyers.', 'icon' => 'image', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                            ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'desc' => 'Replace a list of links with a schedule page that shows events, sells tickets, and collects subscribers.', 'icon' => 'link', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                        ],
                    ],
                    [
                        'label' => 'Scheduling & Tracking',
                        'tools' => [
                            ['name' => 'Calendly', 'route' => 'marketing.replace_calendly', 'desc' => 'Replace appointment scheduling with public event pages, ticketing, and a shareable event calendar.', 'icon' => 'calendar', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                            ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'desc' => 'Replace spreadsheet tracking with automatic attendee management, sales dashboards, and CSV exports.', 'icon' => 'chart', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                            ['name' => 'QR Code Generators', 'route' => 'marketing.replace_qr_code_generators', 'desc' => 'Replace standalone QR tools with tickets that include built-in QR codes and a live check-in dashboard.', 'icon' => 'qr', 'icon_bg' => 'bg-indigo-100 dark:bg-indigo-500/20', 'icon_color' => 'text-indigo-600 dark:text-indigo-400'],
                        ],
                    ],
                    [
                        'label' => 'Planning & Websites',
                        'tools' => [
                            ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace', 'desc' => 'Replace a full website builder with ready-made event pages, integrated ticketing, and no plugins needed.', 'icon' => 'globe', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                            ['name' => 'Notion', 'route' => 'marketing.replace_notion', 'desc' => 'Replace internal workspace planning with public event pages, built-in ticketing, and automatic calendar sync.', 'icon' => 'code', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                            ['name' => 'Trello', 'route' => 'marketing.replace_trello', 'desc' => 'Replace task boards with a full event platform. Public event pages, ticketing, and payments built in.', 'icon' => 'layout', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                        ],
                    ],
                ];
            @endphp

            <div class="space-y-12">
                @foreach ($toolGroups as $group)
                    <div>
                        <h3 class="text-sm font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">{{ $group['label'] }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($group['tools'] as $tool)
                                <a href="{{ route($tool['route']) }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                                    <div class="w-10 h-10 rounded-xl {{ $tool['icon_bg'] }} flex items-center justify-center mb-4">
                                        <x-marketing-icon :icon="$tool['icon']" :class="'w-5 h-5 ' . $tool['icon_color']" />
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">{{ $tool['name'] }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">{{ $tool['desc'] }}</p>
                                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                                        Learn more
                                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-white h-24"></div>

    <!-- Cross-link to Compare -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('marketing.compare') }}" class="group flex items-center justify-between p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-2">
                        Looking for direct platform comparisons?
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400">
                        See how Event Schedule compares to Eventbrite, Luma, and Ticket Tailor.
                    </p>
                </div>
                <svg aria-hidden="true" class="w-6 h-6 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors flex-shrink-0 ml-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to simplify?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free schedule today. No credit card required, no platform fees ever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
