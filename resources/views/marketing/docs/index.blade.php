<x-marketing-layout>
    <x-slot name="title">Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Documentation</x-slot>
    <x-slot name="description">Complete documentation for Event Schedule. User guides, selfhost installation, and developer resources.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Documentation - Event Schedule",
        "description": "Complete documentation for Event Schedule. User guides, selfhost installation, and developer resources.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2024-01-01",
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .doc-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .doc-card:hover {
            transform: translateY(-8px);
        }
        .section-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .section-card:hover {
            transform: translateY(-4px);
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 0.75s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Documentation</span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                <span class="text-gradient">Event Schedule</span> Knowledge Base
            </h1>

            <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                Everything you need to get started, selfhost your own instance, or build integrations.
            </p>
        </div>
    </section>

    <!-- User Guide Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Guide</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Learn how to use Event Schedule</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('marketing.docs.getting_started') }}" class="doc-card block">
                    <div class="rounded-xl border border-blue-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900 dark:to-sky-900 hover:border-blue-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Getting Started</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Create your account and set up your first schedule.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.schedule_basics') }}" class="doc-card block">
                    <div class="rounded-xl border border-blue-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900 dark:to-sky-900 hover:border-blue-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Schedule Basics</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Configure name, type, location, styling, and settings.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-card block">
                    <div class="rounded-xl border border-sky-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900 dark:to-cyan-900 hover:border-sky-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Schedule Styling</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Customize colors, fonts, and branding for your schedule.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-card block">
                    <div class="rounded-xl border border-sky-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900 dark:to-cyan-900 hover:border-sky-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Advanced Schedule Settings</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Sub-schedules, auto import, calendar integrations, and email settings.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.creating_events') }}" class="doc-card block">
                    <div class="rounded-xl border border-sky-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900 dark:to-cyan-900 hover:border-sky-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Creating Events</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Add events manually or import using AI.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.sharing') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-sky-50 dark:from-cyan-900 dark:to-sky-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Sharing</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Embed, share on social, and grow followers.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.newsletters') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900 dark:to-teal-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Newsletters</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Send branded emails to your audience.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.tickets') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900 dark:to-teal-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Selling Tickets</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Set up ticketing and manage sales.</p>
                    </div>
                </a>

                <a href="{{ marketing_url('/features/event-graphics') }}" class="doc-card block">
                    <div class="rounded-xl border border-teal-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900 dark:to-cyan-900 hover:border-teal-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Event Graphics</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Generate shareable images for social media.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.analytics') }}" class="doc-card block">
                    <div class="rounded-xl border border-teal-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900 dark:to-cyan-900 hover:border-teal-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Analytics</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Track views, devices, traffic sources, and conversions.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.account_settings') }}" class="doc-card block">
                    <div class="rounded-xl border border-teal-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-teal-50 to-sky-50 dark:from-teal-900 dark:to-sky-900 hover:border-teal-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Account Settings</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Manage your profile, payments, and API access.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.availability') }}" class="doc-card block">
                    <div class="rounded-xl border border-teal-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-teal-50 to-sky-50 dark:from-teal-900 dark:to-sky-900 hover:border-teal-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Availability Calendar</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Mark your available and unavailable dates.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900 dark:to-teal-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Scan Agenda</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Use AI to scan a photo of a printed agenda and automatically create event parts.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.boost') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900 dark:to-teal-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Boost</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Promote events with automated Facebook and Instagram ads.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.fan_content') }}" class="doc-card block">
                    <div class="rounded-xl border border-cyan-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900 dark:to-teal-900 hover:border-cyan-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Fan Content</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Let fans submit videos and comments on your events.</p>
                    </div>
                </a>

                <a href="{{ route('marketing.docs.polls') }}" class="doc-card block">
                    <div class="rounded-xl border border-blue-200 dark:border-white/10 p-5 h-full bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 hover:border-blue-500/30 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Event Polls</h3>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Add interactive polls to engage your audience at events.</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Link -->
    <section class="bg-white dark:bg-[#0a0a0f] pt-8 pb-0">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('marketing.faq') }}" class="block group">
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5 bg-gray-50 dark:bg-white/5 hover:border-blue-500/30 transition-colors flex items-center gap-4">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/20 flex-shrink-0">
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Frequently Asked Questions</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Quick answers to common questions about Event Schedule.</p>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 ml-auto group-hover:translate-x-1 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    </section>

    <!-- Selfhost & Developer Sections -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Selfhost Section Card -->
                <a href="{{ route('marketing.docs.selfhost') }}" class="section-card block group">
                    <div class="relative overflow-hidden rounded-2xl border border-blue-200 dark:border-white/10 p-8 h-full bg-gradient-to-br from-blue-100 via-blue-100 to-sky-100 dark:from-blue-900 dark:via-blue-900 dark:to-sky-900 group-hover:border-blue-500/30 transition-colors">
                        <!-- Glow effect -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/20 rounded-full blur-[60px] group-hover:bg-blue-500/30 transition-colors"></div>

                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl mb-6 bg-blue-100 dark:bg-blue-500/20">
                                <svg aria-hidden="true" class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Selfhost Installation</h2>
                            <p class="text-gray-500 dark:text-gray-300 mb-6">Deploy Event Schedule on your own server. Complete control, full customization.</p>

                            <div class="space-y-2 mb-6">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Installation Guide
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Stripe & Google Calendar Integration
                                </div>
                            </div>

                            <div class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">
                                View Selfhost Docs
                                <svg aria-hidden="true" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- SaaS Section Card -->
                <a href="{{ route('marketing.docs.saas.setup') }}" class="section-card block group">
                    <div class="relative overflow-hidden rounded-2xl border border-sky-200 dark:border-white/10 p-8 h-full bg-gradient-to-br from-sky-100 via-sky-100 to-blue-100 dark:from-sky-900 dark:via-sky-900 dark:to-blue-900 group-hover:border-sky-500/30 transition-colors">
                        <!-- Glow effect -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-sky-500/20 rounded-full blur-[60px] group-hover:bg-sky-500/30 transition-colors"></div>

                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl mb-6 bg-sky-100 dark:bg-sky-500/20">
                                <svg aria-hidden="true" class="w-7 h-7 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">SaaS Platform</h2>
                            <p class="text-gray-500 dark:text-gray-300 mb-6">Run Event Schedule as a multi-tenant SaaS with subscriptions and event promotion.</p>

                            <div class="space-y-2 mb-6">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    SaaS Multi-tenant Setup
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Meta Ads Boost Integration
                                </div>
                            </div>

                            <div class="inline-flex items-center text-sm font-medium text-sky-600 dark:text-sky-400 group-hover:text-sky-700 dark:group-hover:text-sky-300 transition-colors">
                                View SaaS Docs
                                <svg aria-hidden="true" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- API Reference Card -->
                <a href="{{ route('marketing.docs.developer.api') }}" class="section-card block group">
                    <div class="relative overflow-hidden rounded-2xl border border-emerald-200 dark:border-white/10 p-8 h-full bg-gradient-to-br from-emerald-100 via-teal-100 to-cyan-100 dark:from-emerald-900 dark:via-teal-900 dark:to-cyan-900 group-hover:border-emerald-500/30 transition-colors">
                        <!-- Glow effect -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-[60px] group-hover:bg-emerald-500/30 transition-colors"></div>

                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl mb-6 bg-emerald-100 dark:bg-emerald-500/20">
                                <svg aria-hidden="true" class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>

                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></div>
                                <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">REST API</span>
                            </div>

                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">API Reference</h2>
                            <p class="text-gray-500 dark:text-gray-300 mb-6">Complete REST API documentation with authentication, endpoints, request/response examples, and error handling.</p>

                            <div class="flex flex-wrap items-center gap-4 mb-6">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    JSON responses
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    API key auth
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <svg aria-hidden="true" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Full CRUD
                                </div>
                            </div>

                            <div class="inline-flex items-center text-sm font-medium text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">
                                Explore the API
                                <svg aria-hidden="true" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Glossary Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Glossary</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Key terms used throughout Event Schedule</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Your event calendar with its own unique URL and branding. Each schedule can contain unlimited events.</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Event</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A single occurrence with a date, time, location, and details. Events belong to a schedule.</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedule</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A category to organize events within your schedule (e.g., "Live Music", "Comedy", "Workshops").</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A purchasable item for event attendance. Events can have multiple ticket types (e.g., General, VIP).</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Follower</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Someone who follows your schedule to receive updates about new events.</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Newsletter</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">An email campaign sent to your followers or ticket buyers to announce events, share updates, or promote your schedule.</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Segment</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A defined audience group for sending newsletters (e.g., all followers, ticket buyers, or a custom list).</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Embed</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A widget that displays your schedule on an external website using an iframe or JavaScript snippet.</p>
                </div>
                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Admin Panel</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">The management interface where you configure your schedule, add events, and view analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DefinedTermSet Schema for Glossary -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "DefinedTermSet",
        "name": "Event Schedule Glossary",
        "description": "Key terms used throughout Event Schedule",
        "hasDefinedTerm": [
            {
                "@type": "DefinedTerm",
                "name": "Schedule",
                "description": "Your event calendar with its own unique URL and branding. Each schedule can contain unlimited events."
            },
            {
                "@type": "DefinedTerm",
                "name": "Event",
                "description": "A single occurrence with a date, time, location, and details. Events belong to a schedule."
            },
            {
                "@type": "DefinedTerm",
                "name": "Sub-schedule",
                "description": "A category to organize events within your schedule (e.g., Live Music, Comedy, Workshops)."
            },
            {
                "@type": "DefinedTerm",
                "name": "Ticket",
                "description": "A purchasable item for event attendance. Events can have multiple ticket types (e.g., General, VIP)."
            },
            {
                "@type": "DefinedTerm",
                "name": "Follower",
                "description": "Someone who follows your schedule to receive updates about new events."
            },
            {
                "@type": "DefinedTerm",
                "name": "Newsletter",
                "description": "An email campaign sent to your followers or ticket buyers to announce events, share updates, or promote your schedule."
            },
            {
                "@type": "DefinedTerm",
                "name": "Segment",
                "description": "A defined audience group for sending newsletters (e.g., all followers, ticket buyers, or a custom list)."
            },
            {
                "@type": "DefinedTerm",
                "name": "Embed",
                "description": "A widget that displays your schedule on an external website using an iframe or JavaScript snippet."
            },
            {
                "@type": "DefinedTerm",
                "name": "Admin Panel",
                "description": "The management interface where you configure your schedule, add events, and view analytics."
            }
        ]
    }
    </script>

    <!-- Open Source Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-200 dark:bg-white/10 mb-6">
                    <svg aria-hidden="true" class="w-8 h-8 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    100% Open Source
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8 max-w-xl mx-auto">
                    Event Schedule is fully open source. Explore the code, report issues, or contribute on GitHub.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 border border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white font-medium transition-colors">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        View on GitHub
                    </a>
                    <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 border border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white font-medium transition-colors">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Discussions
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
