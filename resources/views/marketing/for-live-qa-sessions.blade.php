<x-marketing-layout>
    <x-slot name="title">Event Schedule for Live Q&A Sessions | Scheduling Software</x-slot>
    <x-slot name="description">Free live Q&A scheduling software with registration, ticketing, and email notifications. Works with Zoom, YouTube Live, and any platform. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Q&A Sessions</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Live Q&A Sessions",
        "description": "Free live Q&A scheduling software with registration, ticketing, and email notifications. Works with Zoom, YouTube Live, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Q&A Session Hosts"
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
                "name": "Can I collect audience questions before the session?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use the built-in email feature to send question prompts to all registered attendees before your live Q&A session. You can also include a link to a form or discussion thread in the event description so attendees submit questions ahead of time. Your full Q&A schedule lives on one shareable page that your audience can bookmark and revisit."
                }
            },
            {
                "@type": "Question",
                "name": "What streaming platforms work with Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, Twitter Spaces, and custom solutions. Event Schedule is platform-agnostic - just paste your link and attendees join from your schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for live Q&A sessions?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up paid registration with Stripe for premium Q&A sessions, expert AMAs, or exclusive office hours. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for hosting Q&A sessions?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free Q&A hosting software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets."
                }
            }
        ]
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Live Q&A Sessions",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Live Q&A Session Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule live Q&A sessions with built-in registration, audience interaction, ticketing, and streaming link integration. Works with Zoom, YouTube Live, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered attendees",
            "One registration link for all Q&A sessions",
            "Zero-fee ticket sales for paid sessions",
            "Google Calendar two-way sync",
            "Works with Zoom, YouTube Live, Microsoft Teams",
            "Recurring Q&A series scheduling",
            "Attendee registration management",
            "Follower notifications for new sessions",
            "Open source Q&A session platform",
            "Selfhosted Q&A scheduling option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "live Q&A platform, Q&A session scheduling, interactive Q&A events, paid Q&A sessions",
        "screenshot": "{{ asset('images/social/for-live-qa-sessions.png') }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-violet-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-purple-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-fuchsia-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Grid overlay for texture -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Speakers, Hosts & Community Builders</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Host live Q&A sessions that spark real conversations.<br>
                <span class="qa-glow-text">Zero platform fees.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                From AMAs to office hours. Schedule interactive sessions, manage speakers, and let your audience register from one link.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-violet-500/25">
                    Create your Q&A schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-base text-gray-400 dark:text-gray-500 max-w-2xl mx-auto">
                The live Q&A scheduling platform with built-in registration, attendee email notifications, paid ticketing, and Google Calendar sync for speakers, hosts, and community builders.
            </p>

            <!-- Type tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 text-xs font-medium border border-violet-200 dark:border-violet-800/50">AMAs</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-xs font-medium border border-purple-200 dark:border-purple-800/50">Town Halls</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-200 dark:border-fuchsia-800/50">Expert Panels</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 text-xs font-medium border border-violet-200 dark:border-violet-800/50">Fireside Chats</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-xs font-medium border border-purple-200 dark:border-purple-800/50">Community Q&As</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-200 dark:border-fuchsia-800/50">Office Hours</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-violet-400 mb-2">~85%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">higher engagement in interactive formats vs passive</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-purple-400 mb-2">3x</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">more audience retention with live Q&A vs recorded</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-fuchsia-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on paid Q&A sessions</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Everything you need to host live Q&A sessions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Email attendees before sessions (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-violet-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 text-sm font-medium mb-5 border border-violet-200 dark:border-violet-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email Attendees
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Email attendees before sessions</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Send reminders, share question prompts, and follow up with session summaries. Your audience, your inbox.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Session reminders</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Question prompts</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Follow-up links</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-950 dark:to-purple-950 rounded-2xl border border-violet-300 dark:border-violet-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-white text-sm font-semibold">QA</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Q&A Host</div>
                                            <div class="text-violet-600 dark:text-violet-300 text-xs">Session reminder</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-violet-600/30 to-purple-600/30 rounded-xl p-3 border border-violet-400/20">
                                        <div class="text-center">
                                            <div class="text-gray-900 dark:text-white text-xs font-semibold mb-1">TOMORROW AT 3 PM</div>
                                            <div class="text-violet-700 dark:text-violet-300 text-sm font-bold">Ask Me Anything</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">Submit your questions now</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">72%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">38%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell access to premium Q&As -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell access to premium Q&As</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Charge for exclusive sessions. 100% of Stripe payments go to you. See all <a href="{{ route('marketing.ticketing') }}" class="text-emerald-600 dark:text-emerald-400 underline hover:no-underline">ticketing features</a>.</p>

                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4">
                            <div class="text-center mb-3">
                                <div class="text-emerald-700 dark:text-emerald-300 text-xs">You keep</div>
                                <div class="text-gray-900 dark:text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One link for all your sessions -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-purple-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 text-sm font-medium mb-5 border border-purple-200 dark:border-purple-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for all your sessions</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Put it on your website, email signature, or social profiles. All your Q&A sessions in one place.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-purple-500/20 border border-purple-400/30 mb-3">
                            <svg aria-hidden="true" class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourqa</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-purple-700 dark:text-purple-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-purple-700 dark:text-purple-300 text-[10px]">Email</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-purple-700 dark:text-purple-300 text-[10px]">Social</div>
                        </div>
                    </div>
                </div>

                <!-- Works with any streaming platform (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-violet-100 dark:from-fuchsia-900 dark:to-violet-900 border border-fuchsia-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-fuchsia-100 dark:bg-fuchsia-900/40 text-fuchsia-700 dark:text-fuchsia-300 text-sm font-medium mb-5 border border-fuchsia-200 dark:border-fuchsia-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Any Platform
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Works with any streaming platform</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Zoom, Microsoft Teams, YouTube Live, Twitter Spaces, or custom RTMP. Add your link and attendees join from your schedule. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-fuchsia-600 dark:text-fuchsia-400 underline hover:no-underline">online event features</a>.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-violet-100 dark:bg-violet-500/20 rounded-xl border border-violet-400/30 p-4 w-36">
                                    <div class="text-violet-700 dark:text-violet-300 text-xs text-center mb-2 font-semibold">Your Schedule</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-gray-300 dark:bg-white/20 rounded"></div>
                                        <div class="h-2 bg-violet-400/40 rounded w-3/4"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-violet-200 dark:bg-violet-400/20 border border-violet-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">Live AMA</div>
                                        <div class="text-[8px] text-violet-700 dark:text-violet-300 text-center mt-0.5">Fri 3:00 PM</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="w-6 h-6 text-violet-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-violet-600 dark:text-violet-400 text-[10px]">join link</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-36">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">Streaming</div>
                                    <div class="space-y-2 text-center">
                                        <div class="p-1.5 rounded bg-blue-400/20 text-[10px] text-blue-700 dark:text-blue-300">Zoom</div>
                                        <div class="p-1.5 rounded bg-red-400/20 text-[10px] text-red-700 dark:text-red-300">YouTube Live</div>
                                        <div class="p-1.5 rounded bg-purple-400/20 text-[10px] text-purple-700 dark:text-purple-300">Teams</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring Q&A series -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Recurring Q&A series</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Weekly office hours or monthly AMAs. Set it once and let attendees follow along.</p>

                    <div class="bg-amber-500/20 rounded-xl border border-amber-400/30 p-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/20">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-900 dark:text-white text-[10px] font-medium">Fri - Office Hours</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Office Hours</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Office Hours</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Office Hours</span>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-amber-600 dark:text-amber-300 text-[10px]">Repeats weekly</div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Google Calendar sync</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync. Q&A sessions, prep time, and follow-ups all in one place.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-violet-400/80 dark:bg-violet-400/40 rounded text-[6px] text-white px-1">Q&A</div>
                                <div class="h-1.5 bg-amber-400/80 dark:bg-amber-400/40 rounded text-[6px] text-white px-1">Prep</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audience follows your sessions -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900 dark:to-fuchsia-900 border border-violet-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 text-sm font-medium mb-5 border border-violet-200 dark:border-violet-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Audience follows your sessions</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Followers get notified when you schedule new Q&A sessions.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-fuchsia-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-fuchsia-500 to-violet-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-white/20 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-gray-600 dark:text-white text-xs">+156</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-violet-600 dark:text-violet-400 text-xs">159 following your Q&A sessions</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From your first Q&A to a regular series
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your Q&A program
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- First Q&A session -->
                <div class="bg-gradient-to-br from-violet-100 to-violet-50 dark:from-[#15101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-violet-200 dark:border-violet-900/20 hover:border-violet-300 dark:hover:border-violet-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">First Q&A session</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Share a link and host your first live Q&A. Free registration gets your audience in the door.</p>
                </div>

                <!-- Regular office hours -->
                <div class="bg-gradient-to-br from-purple-100 to-purple-50 dark:from-[#15101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-purple-200 dark:border-purple-900/20 hover:border-purple-300 dark:hover:border-purple-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Regular office hours</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Set up weekly or monthly recurring sessions. Your audience follows along and gets notified.</p>
                </div>

                <!-- Paid expert Q&As -->
                <div class="bg-gradient-to-br from-fuchsia-100 to-fuchsia-50 dark:from-[#1a101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-fuchsia-200 dark:border-fuchsia-900/20 hover:border-fuchsia-300 dark:hover:border-fuchsia-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-fuchsia-600 dark:text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paid expert Q&As</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Start charging for premium access. Sell tickets with zero platform fees.</p>
                </div>

                <!-- Multi-speaker panels -->
                <div class="bg-gradient-to-br from-violet-100 to-violet-50 dark:from-[#12101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-violet-200 dark:border-violet-900/20 hover:border-violet-300 dark:hover:border-violet-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multi-speaker panels</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Invite multiple speakers. Organize panels with different experts across sessions.</p>
                </div>

                <!-- Community series -->
                <div class="bg-gradient-to-br from-purple-100 to-purple-50 dark:from-[#15101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-purple-200 dark:border-purple-900/20 hover:border-purple-300 dark:hover:border-purple-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Community series</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Build a following. Followers get notified when you announce new sessions.</p>
                </div>

                <!-- Hybrid town halls -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hybrid town halls</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Combine in-person and virtual attendance. Sell different ticket types for on-site and remote participants.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for every type of live Q&A
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether it's a product AMA or weekly office hours, Event Schedule works for you. Also see Event Schedule for <a href="{{ route('marketing.for_webinars') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Webinars</a> and <a href="{{ route('marketing.for_virtual_conferences') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Tech Founders -->
                <x-sub-audience-card
                    name="Tech Founders"
                    description="Product AMAs, roadmap Q&As, and investor office hours. Answer questions from your community in real-time."
                    icon-color="violet"
                    blog-slug="for-tech-founder-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coaches & Consultants -->
                <x-sub-audience-card
                    name="Coaches & Consultants"
                    description="Client office hours, group coaching Q&As, and expert sessions. Build trust with direct audience interaction."
                    icon-color="purple"
                    blog-slug="for-coach-consultant-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Authors & Thought Leaders -->
                <x-sub-audience-card
                    name="Authors & Thought Leaders"
                    description="Book Q&As, fireside chats, and audience discussions. Connect with readers and followers directly."
                    icon-color="fuchsia"
                    blog-slug="for-author-thought-leader-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Managers -->
                <x-sub-audience-card
                    name="Community Managers"
                    description="Town halls, member Q&As, and community feedback sessions. Keep your community engaged and informed."
                    icon-color="amber"
                    blog-slug="for-community-manager-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Educators & Professors -->
                <x-sub-audience-card
                    name="Educators & Professors"
                    description="Student office hours, exam review sessions, and open Q&As. Make yourself accessible outside the classroom."
                    icon-color="sky"
                    blog-slug="for-educator-professor-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- HR & Internal Teams -->
                <x-sub-audience-card
                    name="HR & Internal Teams"
                    description="All-hands Q&As, leadership town halls, and policy discussions. Keep your organization aligned and transparent."
                    icon-color="emerald"
                    blog-slug="for-hr-internal-team-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Three steps to a packed Q&A session
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-violet-600 to-purple-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-violet-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create your session</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add your topic, date, and streaming link. Set up free or paid registration.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-violet-600 to-purple-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-violet-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Attendees register. Send question prompts before the session.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-violet-600 to-purple-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-violet-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Go live</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Answer questions in real-time. Follow up with a summary.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything Q&A hosts ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I collect audience questions before the session?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Use the built-in email feature to send question prompts to all registered attendees before your live Q&A session. You can also include a link to a form or discussion thread in the event description so attendees submit questions ahead of time. Your full Q&A schedule lives on one shareable page that your audience can bookmark and revisit.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What streaming platforms work with Event Schedule?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, Twitter Spaces, and custom solutions. Event Schedule is platform-agnostic - just paste your link and attendees join from your schedule.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-cyan-100 dark:from-emerald-900 dark:to-cyan-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I charge for live Q&A sessions?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Set up paid registration with Stripe for premium Q&A sessions, expert AMAs, or exclusive office hours. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30).
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for hosting Q&A sessions?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is free Q&A hosting software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets.
                        </p>
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
                    name="Online Events"
                    description="Host virtual events with any streaming platform"
                    :url="marketing_url('/features/online-events')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
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
                    name="Newsletters"
                    description="Send event updates directly to followers' inboxes"
                    :url="marketing_url('/features/newsletters')"
                    icon-color="green"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- Related Pages -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ marketing_url('/for-webinars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Webinars</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-virtual-conferences') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Virtual Conferences</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-online-classes') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Online Classes</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-watch-parties') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Watch Parties</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-violet-200 dark:border-violet-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-violet-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-purple-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your sessions. Your audience. No middleman.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stop paying platform fees. Fill your Q&A sessions.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-violet-500/20">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-gray-500 text-sm">No credit card required</p>
        </div>
    </section>


    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to host a live Q&A session with Event Schedule",
        "description": "Three steps to schedule and host your live Q&A session online.",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Create your session",
                "text": "Add your topic, date, and streaming link. Set up free or paid registration."
            },
            {
                "@type": "HowToStep",
                "name": "Share your link",
                "text": "Attendees register. Send question prompts before the session."
            },
            {
                "@type": "HowToStep",
                "name": "Go live",
                "text": "Answer questions in real-time. Follow up with a summary."
            }
        ]
    }
    </script>

    <style {!! nonce_attr() !!}>
        .qa-glow-text {
            background: linear-gradient(135deg, #7c3aed, #a855f7, #d946ef);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(124, 58, 237, 0.3);
        }
        .dark .qa-glow-text {
            background: linear-gradient(135deg, #a78bfa, #c084fc, #e879f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(167, 139, 250, 0.3);
        }
    </style>
</x-marketing-layout>
