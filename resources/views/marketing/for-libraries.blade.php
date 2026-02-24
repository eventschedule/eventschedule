<x-marketing-layout>
    <x-slot name="title">Event Schedule for Libraries | Program Calendar</x-slot>
    <x-slot name="description">From story time to author readings. One calendar for all your library programs. Keep patrons engaged and informed. Email your community directly. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Libraries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Libraries",
        "description": "One calendar for all your library programs from story time to author readings. Email your community directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Libraries"
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
                "name": "Is Event Schedule free for libraries?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your program schedule, building a patron following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage story times, author events, and workshops together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by program type - children's story times, author talks, book clubs, technology workshops, and community meetings. Each program can have its own description, images, and registration details."
                }
            },
            {
                "@type": "Question",
                "name": "How do patrons find out about library programs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Patrons can follow your library's schedule and receive email notifications for new programs. Embed your calendar on your library website, share on social media, or send newsletters to your community."
                }
            },
            {
                "@type": "Question",
                "name": "Can patrons register for programs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable registration on any event to manage attendance and capacity. Patrons receive confirmation and reminders. For paid programs, connect Stripe to handle payments with zero platform fees."
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
        "name": "Event Schedule for Libraries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Library Program Management Software",
        "operatingSystem": "Web",
        "description": "From story time to author readings. One calendar for all your library programs. Keep patrons engaged and informed. Email your community directly and fill your programs. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Program update newsletters",
            "Recurring program scheduling",
            "Author event management",
            "Community program categories",
            "Zero fee ticketing and registration",
            "Google Calendar sync",
            "Team management for library staff",
            "Program attendance analytics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with sky/blue tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <!-- Indigo accent -->
            <div class="absolute top-40 right-1/3 w-[200px] h-[200px] bg-sky-500/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Bookshelf SVG pattern -->
        <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.06] overflow-hidden">
            <svg aria-hidden="true" width="100%" height="100%">
                <defs>
                    <pattern id="bookshelf" x="0" y="0" width="200" height="80" patternUnits="userSpaceOnUse">
                        <!-- Shelf line -->
                        <line x1="0" y1="78" x2="200" y2="78" stroke="#475569" stroke-width="2"/>
                        <!-- Book spines - varying heights and colors -->
                        <rect x="8" y="15" width="10" height="63" rx="1" fill="#0ea5e9"/>
                        <rect x="22" y="25" width="8" height="53" rx="1" fill="#0EA5E9"/>
                        <rect x="34" y="10" width="12" height="68" rx="1" fill="#3b82f6"/>
                        <rect x="50" y="20" width="9" height="58" rx="1" fill="#4E81FA"/>
                        <rect x="63" y="30" width="11" height="48" rx="1" fill="#0ea5e9"/>
                        <rect x="78" y="8" width="10" height="70" rx="1" fill="#475569"/>
                        <rect x="92" y="22" width="8" height="56" rx="1" fill="#0EA5E9"/>
                        <rect x="104" y="15" width="12" height="63" rx="1" fill="#3b82f6"/>
                        <rect x="120" y="28" width="9" height="50" rx="1" fill="#0ea5e9"/>
                        <rect x="133" y="12" width="11" height="66" rx="1" fill="#4E81FA"/>
                        <rect x="148" y="18" width="10" height="60" rx="1" fill="#475569"/>
                        <rect x="162" y="25" width="8" height="53" rx="1" fill="#0EA5E9"/>
                        <rect x="174" y="10" width="12" height="68" rx="1" fill="#3b82f6"/>
                        <rect x="190" y="20" width="8" height="58" rx="1" fill="#0ea5e9"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#bookshelf)"/>
            </svg>
        </div>

        <!-- Reading lamp warm glow -->
        <div class="absolute top-0 left-0 w-[60%] h-[60%] bg-amber-400/[0.04] dark:bg-amber-400/[0.03] rounded-full blur-[150px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex flex-col items-center px-5 py-2.5 rounded-sm library-card-badge mb-8 backdrop-blur-sm animate-reveal" style="opacity: 0;">
                <div class="flex items-center gap-2">
                    <svg aria-hidden="true" class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">For Libraries</span>
                </div>
                <div class="w-full h-px bg-sky-400/30 mt-1.5"></div>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Your programs. Your patrons.<br>
                <span class="text-gradient-sky">Always booked.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                From story time to author readings. One calendar for all your programs. Keep patrons engaged and informed.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-blue-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-sky-500/25">
                    Create your program calendar
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Library program tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-500/30">Author Readings</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-500/30">Book Clubs</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-500/30">Story Time</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-500/30">Film Screenings</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-500/30">Workshops</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-500/30">Maker Space</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Program Updates Newsletter - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">New program? Your patrons are first to know.</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Summer reading kickoff, new author visit, special workshops - one click emails everyone who signed up. No algorithm decides who sees your announcements.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your patrons, direct reach</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No middleman</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-950 dark:to-blue-950 rounded-2xl border border-sky-300 dark:border-sky-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">This Month's Programs</div>
                                            <div class="text-sky-700 dark:text-sky-300 text-xs">Sending to 1,847 patrons...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Author Visit: Jane Smith</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Summer Reading Kickoff</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Teen Maker Space Workshop</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring Programs -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Weekly programs, set once</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Story time every Tuesday, book club every month. Set it once and it appears automatically.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-500/20 border border-blue-500/30">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Toddler Story Time</span>
                            <span class="ml-auto text-blue-700 dark:text-blue-300 text-xs">Tue</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Teen Book Club</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">Wed</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Craft Hour</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">Thu</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Film Fridays</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">Fri</span>
                        </div>
                    </div>
                </div>

                <!-- Author Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Author Events
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Showcase visiting authors</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Author readings, book signings, and Q&A sessions. Let patrons register and never miss an event.</p>

                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-800 dark:to-blue-900 rounded-xl border border-blue-300/50 dark:border-blue-600/30 p-4 w-48 shadow-lg transform -rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-blue-800 dark:text-blue-200 text-[10px] tracking-widest uppercase">Author Visit</div>
                                <div class="text-blue-900 dark:text-blue-100 text-sm font-serif font-semibold mt-1">Jane Smith</div>
                                <div class="w-16 h-20 bg-gradient-to-br from-blue-200 to-blue-300 dark:from-blue-700 dark:to-blue-800 rounded mx-auto mt-2 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="text-blue-600 dark:text-blue-300 text-[10px] mt-2">Sat, Mar 15 &bull; 2 PM</div>
                                <div class="text-blue-500 dark:text-blue-400 text-[9px] mt-1">Reading & Signing</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Community Programs (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Community Programs
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Programs for every age group</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">From toddler story time to senior tech help. Organize programs by audience so patrons find exactly what they need.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Filter by age group</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Easy to browse</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-sky-500/20 border border-sky-400/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">&#128118;</div>
                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Children</div>
                                <div class="text-sky-700 dark:text-sky-300 text-xs mt-1">12 programs</div>
                            </div>
                            <div class="bg-sky-500/20 border border-sky-400/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">&#129680;</div>
                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Teen</div>
                                <div class="text-sky-700 dark:text-sky-300 text-xs mt-1">8 programs</div>
                            </div>
                            <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">&#128100;</div>
                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Adult</div>
                                <div class="text-blue-700 dark:text-blue-300 text-xs mt-1">10 programs</div>
                            </div>
                            <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">&#128116;</div>
                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Senior</div>
                                <div class="text-blue-700 dark:text-blue-300 text-xs mt-1">6 programs</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zero Fee Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Free Tickets
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Zero fee registration</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Free tickets for free programs. Manage capacity for story time, workshops, and special events without any fees.</p>

                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-emerald-100 to-green-50 dark:from-emerald-800 dark:to-green-900 rounded-xl border border-emerald-300/50 dark:border-emerald-600/30 p-4 w-44 shadow-lg transform rotate-1 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-emerald-800 dark:text-emerald-200 text-[10px] tracking-widest uppercase">Free Event</div>
                                <div class="text-emerald-900 dark:text-emerald-100 text-sm font-serif font-semibold mt-1">Story Time</div>
                                <div class="text-emerald-700 dark:text-emerald-300 text-xl font-bold mt-2">FREE</div>
                                <div class="text-emerald-600 dark:text-emerald-300 text-[10px] mt-1">Tuesdays &bull; 10 AM</div>
                                <div class="text-emerald-500 dark:text-emerald-400 text-[9px] mt-1">15 spots remaining</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Google Calendar
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sync with Google Calendar</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync keeps your library's Google Calendar and Event Schedule in perfect harmony.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-2xl shadow-lg flex items-center justify-center mx-auto">
                                <svg aria-hidden="true" class="w-10 h-10" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10z" opacity="0.1"/>
                                    <path fill="#4285F4" d="M12 7v5l4.28 2.54.72-1.21-3.5-2.08V7H12z"/>
                                    <path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12h2c0-4.42 3.58-8 8-8V2z"/>
                                    <path fill="#FBBC05" d="M2 12c0 5.52 4.48 10 10 10v-2c-4.42 0-8-3.58-8-8H2z"/>
                                    <path fill="#34A853" d="M12 22c5.52 0 10-4.48 10-10h-2c0 4.42-3.58 8-8 8v2z"/>
                                    <path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10v2c4.42 0 8 3.58 8 8h2z"/>
                                </svg>
                            </div>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <div class="w-8 h-0.5 bg-blue-400/50"></div>
                                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <div class="w-8 h-0.5 bg-blue-400/50"></div>
                            </div>
                            <div class="text-blue-700 dark:text-blue-300 text-xs font-medium text-center mt-2">Two-way sync</div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Track program attendance</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">See which programs draw the most patrons. Make data-driven decisions for your programming.</p>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Story Time</span>
                                <span class="text-cyan-700 dark:text-cyan-300">94%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-500 rounded-full" style="width: 94%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Book Club</span>
                                <span class="text-cyan-700 dark:text-cyan-300">78%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-500 rounded-full" style="width: 78%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Tech Help</span>
                                <span class="text-cyan-700 dark:text-cyan-300">62%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-500 rounded-full" style="width: 62%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Monthly Programming Calendar - UNIQUE TO LIBRARIES -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your month of programs
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Every program, every age group, every week. Patrons see what's happening and sign up in seconds.
                </p>
            </div>

            <!-- Programming Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Toddler Story Time -->
                <div class="catalog-card bg-amber-50/80 dark:bg-sky-900/40 rounded-sm border border-amber-200/60 dark:border-sky-500/20 p-5 relative overflow-hidden group hover:border-sky-500/40 transition-colors">
                    <!-- Index tab -->
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-sky-400/60 dark:bg-sky-400/40 rounded-b-sm"></div>
                    <!-- Dewey number -->
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">028.5</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/20 flex items-center justify-center">
                            <span class="text-sm">&#128214;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Toddler Story Time</div>
                            <div class="text-sky-700 dark:text-sky-300 text-xs">Tuesdays &bull; 10:00 AM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-sky-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">Ages 0-3 &bull; Children's Room</span>
                    </div>
                </div>

                <!-- Teen Coding Club -->
                <div class="catalog-card bg-amber-50/80 dark:bg-sky-900/40 rounded-sm border border-amber-200/60 dark:border-sky-500/20 p-5 relative overflow-hidden group hover:border-sky-500/40 transition-colors">
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-sky-400/60 dark:bg-sky-400/40 rounded-b-sm"></div>
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">005.1</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/20 flex items-center justify-center">
                            <span class="text-sm">&#128187;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Teen Coding Club</div>
                            <div class="text-sky-700 dark:text-sky-300 text-xs">Wednesdays &bull; 4:00 PM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-sky-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">Ages 13-18 &bull; Maker Space</span>
                    </div>
                </div>

                <!-- Adult Book Club -->
                <div class="catalog-card bg-amber-50/80 dark:bg-blue-900/40 rounded-sm border border-amber-200/60 dark:border-blue-500/20 p-5 relative overflow-hidden group hover:border-blue-500/40 transition-colors">
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-blue-400/60 dark:bg-blue-400/40 rounded-b-sm"></div>
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">813.5</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <span class="text-sm">&#128218;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Adult Book Club</div>
                            <div class="text-blue-700 dark:text-blue-300 text-xs">1st Thursday &bull; 7:00 PM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">Adults &bull; Meeting Room</span>
                    </div>
                </div>

                <!-- Senior Tech Help -->
                <div class="catalog-card bg-amber-50/80 dark:bg-teal-900/40 rounded-sm border border-amber-200/60 dark:border-teal-500/20 p-5 relative overflow-hidden group hover:border-teal-500/40 transition-colors">
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-teal-400/60 dark:bg-teal-400/40 rounded-b-sm"></div>
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">004.0</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center">
                            <span class="text-sm">&#128421;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Senior Tech Help</div>
                            <div class="text-teal-700 dark:text-teal-300 text-xs">Fridays &bull; 1:00 PM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">Seniors &bull; Computer Lab</span>
                    </div>
                </div>

                <!-- Author Readings -->
                <div class="catalog-card bg-amber-50/80 dark:bg-blue-900/40 rounded-sm border border-amber-200/60 dark:border-blue-500/20 p-5 relative overflow-hidden group hover:border-blue-500/40 transition-colors">
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-blue-400/60 dark:bg-blue-400/40 rounded-b-sm"></div>
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">808.0</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <span class="text-sm">&#9997;&#65039;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Author Readings</div>
                            <div class="text-blue-700 dark:text-blue-300 text-xs">Saturdays &bull; 2:00 PM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">All Ages &bull; Main Hall</span>
                    </div>
                </div>

                <!-- Film Screenings -->
                <div class="catalog-card bg-amber-50/80 dark:bg-rose-900/40 rounded-sm border border-amber-200/60 dark:border-rose-500/20 p-5 relative overflow-hidden group hover:border-rose-500/40 transition-colors">
                    <div class="absolute -top-px left-4 w-12 h-2.5 bg-rose-400/60 dark:bg-rose-400/40 rounded-b-sm"></div>
                    <div class="absolute top-2 right-3 text-[9px] font-mono text-gray-400 dark:text-gray-500">791.4</div>
                    <div class="flex items-center gap-2 mb-3 mt-1">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center">
                            <span class="text-sm">&#127916;</span>
                        </div>
                        <div>
                            <div class="text-gray-900 dark:text-white text-sm font-semibold">Film Screenings</div>
                            <div class="text-rose-700 dark:text-rose-300 text-xs">2nd Saturday &bull; 6:00 PM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">All Ages &bull; Community Room</span>
                    </div>
                </div>
            </div>

            <!-- Recurring programs note -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">All recurring programs in one place - patrons see what's happening every day</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of libraries
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    From public branches to university collections.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Public Libraries -->
                <x-sub-audience-card
                    name="Public Libraries"
                    description="Community programs, story times, workshops, and author events. Keep your neighborhood informed and engaged."
                    icon-color="sky"
                    blog-slug="for-public-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- University Libraries -->
                <x-sub-audience-card
                    name="University Libraries"
                    description="Lectures, research workshops, study sessions, and academic events. Reach students and faculty directly."
                    icon-color="indigo"
                    blog-slug="for-university-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Reading Rooms -->
                <x-sub-audience-card
                    name="Community Reading Rooms"
                    description="Reading groups, literacy programs, and neighborhood book exchanges. Build a culture of reading."
                    icon-color="blue"
                    blog-slug="for-community-reading-rooms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Children's Libraries -->
                <x-sub-audience-card
                    name="Children's Libraries"
                    description="Story times, crafts, summer reading programs, and educational events. Make reading fun for every child."
                    icon-color="pink"
                    blog-slug="for-childrens-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Archive & Research Centers -->
                <x-sub-audience-card
                    name="Archive & Research Centers"
                    description="Exhibitions, lectures, guided tours, and research workshops. Share your collections with the public."
                    icon-color="slate"
                    blog-slug="for-archive-research-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Mobile Libraries -->
                <x-sub-audience-card
                    name="Mobile Libraries"
                    description="Bookmobile stops, pop-up reading events, and outreach programs. Bring the library to your community."
                    icon-color="teal"
                    blog-slug="for-mobile-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Get your library's program calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-blue-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your library</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Sign up and add your library details. Set up program categories for children, teens, adults, and seniors.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-blue-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your program calendar</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add story times, book clubs, author events, and workshops. Set up recurring programs once.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-blue-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Engage your community</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Patrons follow your calendar and get direct updates. No algorithm between you and your community.
                    </p>
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
                <x-feature-link-card
                    name="Recurring Events"
                    description="Set events to repeat weekly on chosen days"
                    :url="marketing_url('/features/recurring-events')"
                    icon-color="lime"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
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
                <a href="{{ marketing_url('/for-community-centers') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Community Centers</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-spoken-word') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Spoken Word</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-workshop-instructors') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Workshop Instructors</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-theaters') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Theaters</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
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
                    Everything librarians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for libraries?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is free forever for sharing your program schedule, building a patron following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I manage story times, author events, and workshops together?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Use sub-schedules to organize by program type - children's story times, author talks, book clubs, technology workshops, and community meetings. Each program can have its own description, images, and registration details.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-cyan-100 dark:from-emerald-900 dark:to-cyan-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do patrons find out about library programs?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Patrons can follow your library's schedule and receive email notifications for new programs. Embed your calendar on your library website, share on social media, or send newsletters to your community.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can patrons register for programs?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Enable registration on any event to manage attendance and capacity. Patrons receive confirmation and reminders. For paid programs, connect Stripe to handle payments with zero platform fees.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-sky-600 to-blue-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>
        <!-- Blue accent glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-sky-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your patrons deserve better than a bulletin board
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your community directly. Fill your programs. Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-sky-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>


    <style {!! nonce_attr() !!}>
        .text-gradient-sky {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient-sky {
            background: linear-gradient(135deg, #38bdf8, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .library-card-badge {
            background: rgba(255, 251, 235, 0.8);
            border: 1px solid rgba(14, 165, 233, 0.2);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .dark .library-card-badge {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(14, 165, 233, 0.2);
        }

        .catalog-card {
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .catalog-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .dark .catalog-card {
            background: transparent;
        }

        .dark .catalog-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
    </style>
</x-marketing-layout>
