<x-marketing-layout>
    <x-slot name="title">Fan Videos & Comments for Events | Community Engagement - Event Schedule</x-slot>
    <x-slot name="description">Let fans share YouTube videos and comments on your event pages for free. Organizer approval workflow, agenda integration, and recurring event support. No credit card required.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Fan Videos & Comments</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Are fan videos moderated before appearing?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. All fan-submitted videos and comments require your approval before they appear on your schedule. You have full control over what gets published."
                }
            },
            {
                "@type": "Question",
                "name": "What video platforms are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can share videos from YouTube, Vimeo, and other major platforms by pasting a link. The video is embedded directly on your event page."
                }
            },
            {
                "@type": "Question",
                "name": "Where do fan videos appear?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Approved videos and comments appear on the individual event page where they were submitted. They help build social proof and community engagement around your events."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Custom rose/orange gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #e11d48 0%, #f97316 50%, #fb923c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #fb7185 0%, #fb923c 50%, #fdba74 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern" aria-hidden="true"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-rose-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Fan Engagement</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Fan videos &<br>
                <span class="text-gradient">comments</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Let fans and attendees sign in and share YouTube videos and comments on your events. All submissions need your approval. Turn every show into a shared memory.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-orange-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-rose-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Community-powered event pages</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Row 1: YouTube Videos (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                YouTube Videos
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Fan video clips</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Fans share their favorite moments by adding YouTube video links to your event pages. Videos appear as embedded players, creating a rich media gallery of every show.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">YouTube embeds</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Fan submissions</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Duplicate prevention</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto" aria-hidden="true">
                            <div class="relative animate-float">
                                <!-- Video card mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-56">
                                    <div class="text-xs text-gray-500 dark:text-white/70 mb-2">Jazz Night</div>
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mb-3">Fri, Mar 15 at 8 PM</div>
                                    <!-- Video thumbnail -->
                                    <div class="bg-gray-800 rounded-lg aspect-video mb-2 flex items-center justify-center">
                                        <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-600 dark:text-gray-300">Uploaded by Sarah M.</div>
                                    <!-- Second video -->
                                    <div class="bg-gray-800 rounded-lg aspect-video mt-2 mb-2 flex items-center justify-center opacity-60">
                                        <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-600 dark:text-gray-300">Uploaded by Mike T.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Comments -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Comments
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Attendee feedback</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Attendees leave text comments (up to 1,000 characters) on events to share their experience and connect with other fans.</p>

                    <div class="space-y-3" aria-hidden="true">
                        <div class="flex items-start gap-2">
                            <div class="w-7 h-7 rounded-full bg-rose-300 dark:bg-rose-500/40 flex-shrink-0"></div>
                            <div class="bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-xs text-gray-600 dark:text-gray-300">Amazing show tonight! The energy was incredible.</div>
                        </div>
                        <div class="flex items-start gap-2">
                            <div class="w-7 h-7 rounded-full bg-orange-300 dark:bg-orange-500/40 flex-shrink-0"></div>
                            <div class="bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-xs text-gray-600 dark:text-gray-300">Best night ever! Can't wait for the next one.</div>
                        </div>
                        <div class="flex items-start gap-2">
                            <div class="w-7 h-7 rounded-full bg-amber-300 dark:bg-amber-500/40 flex-shrink-0"></div>
                            <div class="bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-xs text-gray-600 dark:text-gray-300">Loved every minute of it!</div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Approval Workflow -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Moderation
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Approval workflow</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">All submissions default to pending. You approve or reject every video and comment from the event edit page before it goes live.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <!-- Pending item -->
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-600 dark:text-gray-300">New video from Sarah</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Pending</span>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <div role="presentation" class="flex-1 text-[10px] py-1 rounded-lg bg-emerald-500 text-white text-center">Approve</div>
                                <div role="presentation" class="flex-1 text-[10px] py-1 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-center">Reject</div>
                            </div>
                        </div>
                        <!-- Approved item -->
                        <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-xl p-3 border border-emerald-200 dark:border-emerald-400/30">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-300">Comment from Mike</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Approved</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Agenda Integration (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Per-Segment
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Agenda integration</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Attach videos and comments to individual agenda segments. Fans can share feedback on specific parts of your event, not just the event as a whole.</p>
                        </div>
                        <div aria-hidden="true">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 space-y-3">
                                <!-- Agenda item 1 -->
                                <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white mb-1">Opening Act</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">8:00 PM - 8:30 PM</div>
                                    <div class="flex gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            2 videos
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[10px] text-orange-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            5 comments
                                        </span>
                                    </div>
                                </div>
                                <!-- Agenda item 2 -->
                                <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white mb-1">Main Performance</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">8:30 PM - 10:00 PM</div>
                                    <div class="flex gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            7 videos
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[10px] text-orange-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            12 comments
                                        </span>
                                    </div>
                                </div>
                                <!-- Agenda item 3 -->
                                <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white mb-1">Encore</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">10:00 PM - 10:30 PM</div>
                                    <div class="flex gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            3 videos
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[10px] text-orange-500">
                                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            8 comments
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Recurring Events (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Recurring
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Per-occurrence content</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">For recurring events, fan videos and comments are tied to specific occurrence dates. Each week's show gets its own collection of fan content, keeping memories organized.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Date-specific</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Organized history</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Browse by date</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto" aria-hidden="true">
                            <div class="relative animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-56">
                                    <!-- Date selector -->
                                    <div class="flex gap-1 mb-3">
                                        <div class="flex-1 text-center py-1 rounded-lg bg-gray-200 dark:bg-white/10 text-[10px] text-gray-500 dark:text-gray-400">Mar 1</div>
                                        <div class="flex-1 text-center py-1 rounded-lg bg-rose-500 text-[10px] text-white font-medium">Mar 8</div>
                                        <div class="flex-1 text-center py-1 rounded-lg bg-gray-200 dark:bg-white/10 text-[10px] text-gray-500 dark:text-gray-400">Mar 15</div>
                                    </div>
                                    <!-- Content for selected date -->
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">Mar 8 content</div>
                                    <div class="space-y-2">
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2 border border-gray-200 dark:border-white/10">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-4 h-4 bg-red-600 rounded flex items-center justify-center">
                                                    <svg aria-hidden="true" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                </div>
                                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Front row view</span>
                                            </div>
                                        </div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2 border border-gray-200 dark:border-white/10">
                                            <div class="text-[10px] text-gray-600 dark:text-gray-300">"Best set this week!"</div>
                                        </div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2 border border-gray-200 dark:border-white/10">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-4 h-4 bg-red-600 rounded flex items-center justify-center">
                                                    <svg aria-hidden="true" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                </div>
                                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Drum solo clip</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Community Building -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Community
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Shared memories</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Turn shows into shared experiences. Every event page becomes a living archive of fan content that grows over time.</p>

                    <div class="flex flex-col items-center gap-3" aria-hidden="true">
                        <!-- Activity bar chart mockup -->
                        <div class="w-full bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-3">Fan engagement over time</div>
                            <div class="flex items-end justify-between h-16 gap-1">
                                <div class="flex-1 bg-rose-500/30 rounded-t" style="height: 30%"></div>
                                <div class="flex-1 bg-rose-500/40 rounded-t" style="height: 50%"></div>
                                <div class="flex-1 bg-rose-500/50 rounded-t" style="height: 70%"></div>
                                <div class="flex-1 bg-rose-500/60 rounded-t" style="height: 90%"></div>
                                <div class="flex-1 bg-rose-500/70 rounded-t" style="height: 100%"></div>
                                <div class="flex-1 bg-rose-500/75 rounded-t" style="height: 85%"></div>
                                <div class="flex-1 bg-rose-500/80 rounded-t" style="height: 95%"></div>
                            </div>
                            <div class="flex justify-between mt-2">
                                <span class="text-[9px] text-gray-400 dark:text-gray-500">Week 1</span>
                                <span class="text-[9px] text-gray-400 dark:text-gray-500">Week 7</span>
                            </div>
                        </div>
                    </div>
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
                    Everything you need to know about fan videos and comments.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: blue-to-sky -->
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Are fan videos moderated before appearing?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Yes. All fan-submitted videos and comments require your approval before they appear on your schedule. You have full control over what gets published.</p>
                    </div>
                </div>

                <!-- Q2: blue-to-cyan -->
                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">What video platforms are supported?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Fans can share videos from YouTube, Vimeo, and other major platforms by pasting a link. The video is embedded directly on your event page.</p>
                    </div>
                </div>

                <!-- Q3: emerald-to-teal -->
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Where do fan videos appear?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Approved videos and comments appear on the individual event page where they were submitted. They help build social proof and community engagement around your events.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-gray-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-slate-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Next feature: Open Source & API -->
                <a href="{{ marketing_url('/open-source') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-gray-100 to-slate-100 dark:from-gray-900 dark:to-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">Open Source & API</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">100% open source. Selfhost on your own server or integrate with our REST API.</p>
                        <span class="inline-flex items-center text-gray-500 dark:text-gray-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- View all features -->
                <a href="{{ marketing_url('/features') }}" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 mb-6">
                            <svg class="w-6 h-6 text-rose-500 dark:text-rose-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">View all features</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Explore ticketing, calendar sync, embed widgets, and everything else Event Schedule offers.</p>
                        <span class="inline-flex items-center text-rose-500 dark:text-rose-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            See features
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-musicians') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Musicians</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-comedians') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Comedians</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-dance-groups') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Dance Groups</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Related Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Newsletters"
                    description="Send branded newsletters to followers and ticket buyers"
                    :url="marketing_url('/features/newsletters')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Online Events"
                    description="Host virtual events with Zoom, Google Meet, and more"
                    :url="marketing_url('/features/online-events')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Embed Calendar"
                    description="Embed your event schedule on any website"
                    :url="marketing_url('/features/embed-calendar')"
                    icon-color="teal"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-rose-600 to-orange-600 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay" aria-hidden="true"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Build community around your events
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Let fans share their favorite moments. No credit card required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-rose-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Fan Videos & Comments",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Engagement Software",
        "operatingSystem": "Web",
        "description": "Let fans share YouTube videos and comments on your event pages for free. Organizer approval workflow, agenda integration, and recurring event support. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "YouTube video submissions",
            "Text comments",
            "Organizer approval workflow",
            "Agenda segment integration",
            "Recurring event support",
            "Community building"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
