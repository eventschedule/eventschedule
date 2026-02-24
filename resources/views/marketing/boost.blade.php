<x-marketing-layout>
    <x-slot name="title">Boost Your Events | Automated Ad Campaigns for Events - Event Schedule</x-slot>
    <x-slot name="description">Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.</x-slot>
    <x-slot name="breadcrumbTitle">Boost</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Boost",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Advertising Automation"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Does Boost guarantee ticket sales or attendance?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost increases your event's visibility by placing ads in front of relevant audiences on Facebook and Instagram. It does not guarantee ticket sales, RSVPs, or attendance. Results depend on your event, audience, creative, and budget."
                }
            },
            {
                "@type": "Question",
                "name": "How much does Boost cost?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You set your own ad budget from $10 to $1,000. Event Schedule charges a transparent service fee on top of your ad spend. There are no hidden costs, and you can see the full breakdown before you launch."
                }
            },
            {
                "@type": "Question",
                "name": "Which platforms do Boost ads run on?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost ads run on Facebook and Instagram, including feeds, Stories, and Reels placements. Your ad is automatically formatted for each placement."
                }
            },
            {
                "@type": "Question",
                "name": "How does targeting work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost uses your event's location, category, and details to build a relevant audience automatically. You can also customize age range, interests, and radius. Advanced mode lets you fine-tune every parameter."
                }
            },
            {
                "@type": "Question",
                "name": "Can I pause or cancel a Boost campaign?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can pause or cancel a running campaign at any time from your dashboard. If you cancel before any budget is spent, you receive a full refund. If Meta rejects the ad, you also receive a full refund. When a campaign completes, any unspent budget and the proportional service fee are automatically refunded."
                }
            },
            {
                "@type": "Question",
                "name": "What happens to unspent budget?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "If Meta rejects your ad or you cancel before any budget is spent, you receive a full refund. When a campaign completes with unspent budget, the remaining ad spend and the proportional service fee are automatically refunded. You receive email notifications when your campaign is created, when 75% of your budget has been spent, and when it completes with final stats."
                }
            },
            {
                "@type": "Question",
                "name": "Do I need a Meta (Facebook) Ads account?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule handles the ad account, creative, and delivery on your behalf. You do not need to create or manage a Meta Ads account."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Custom orange/amber gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #ea580c 0%, #d97706 50%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #fb923c 0%, #fbbf24 50%, #fcd34d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Event Boost</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Your events,<br>
                <span class="text-gradient">amplified</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Turn your event details into live Facebook and Instagram ads. Set a budget, pick your audience, and launch in minutes. No ad manager required.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-orange-600 to-amber-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-orange-500/25">
                    Get started free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400">
                <a href="{{ marketing_url('/docs/boost') }}" class="underline hover:text-orange-600 dark:hover:text-orange-400 transition-colors">Read the Boost guide</a>
            </p>

        </div>
    </section>

    <!-- How It Works -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-16 text-center">How it works</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-500/20 border border-orange-200 dark:border-orange-500/30 mb-6">
                        <span class="text-xl font-bold text-orange-600 dark:text-orange-400">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Pick your event</h3>
                    <p class="text-gray-600 dark:text-gray-400">Choose any upcoming event from your schedule. Boost pulls in the title, date, location, and image automatically.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 mb-6">
                        <span class="text-xl font-bold text-amber-600 dark:text-amber-400">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Set your budget</h3>
                    <p class="text-gray-600 dark:text-gray-400">Slide from $10 to $1,000. See estimated reach before you commit. Pay with the card on file.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-yellow-100 dark:bg-yellow-500/20 border border-yellow-200 dark:border-yellow-500/30 mb-6">
                        <span class="text-xl font-bold text-yellow-600 dark:text-yellow-400">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Launch and track</h3>
                    <p class="text-gray-600 dark:text-gray-400">Your ad goes live on Facebook and Instagram. Watch impressions, reach, and clicks roll in from your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Built for event promotion</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Row 1: Smart Targeting (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Smart Targeting
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Reach the right people</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Your event's location, category, and details are used to build a relevant audience automatically. Boost auto-detects whether your event is in-person, online, or hybrid and adjusts targeting and creative accordingly. Target by radius, age range, and interests without touching an ad manager.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Location radius</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Interest targeting</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Age range</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Targeting mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-56">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-400/30">
                                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                                            <span class="text-gray-900 dark:text-white text-sm">10 mi radius</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Live music fans</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Ages 21 to 45</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Nightlife</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Instant Ad Creation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        One Click
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Instant ad creation</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Your event title, image, date, and location become a polished ad in seconds. No copywriting or design work needed.</p>

                    <div class="flex justify-center">
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 w-40">
                            <div class="bg-orange-200 dark:bg-orange-800 rounded-lg h-16 mb-2 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-6 h-6 text-orange-500 dark:text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="text-[10px] font-bold text-gray-900 dark:text-white">Jazz Night at The Blue Note</div>
                            <div class="text-[9px] text-gray-500 dark:text-gray-400">Sat, Mar 15 - 8 PM</div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Budget Control -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Flexible
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Budget control</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Slide from $10 to $1,000. See estimated reach before you pay. No surprises, no overruns.</p>

                    <!-- Budget slider mockup -->
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Budget</span>
                            <span class="font-bold text-gray-900 dark:text-white">$75</span>
                        </div>
                        <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full" style="width: 15%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>$10</span>
                            <span>$1,000</span>
                        </div>
                        <div class="text-center text-sm text-orange-600 dark:text-orange-400 font-medium">Est. 3,200 to 8,500 reach</div>
                    </div>
                </div>

                <!-- Row 2: Real-time Analytics (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                Real-time Stats
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">See what's working</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Track impressions, reach, clicks, and click-through rate as your campaign runs. All stats update in real time on your dashboard.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Impressions</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Reach</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Clicks</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Click-through rate</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-64">
                                    <!-- Stats cards -->
                                    <div class="grid grid-cols-2 gap-2 mb-3">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">12,480</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Impressions</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">8,320</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Reach</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-amber-600 dark:text-amber-400">412</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Clicks</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">3.3%</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">CTR</div>
                                        </div>
                                    </div>
                                    <!-- Mini bar chart -->
                                    <div class="flex items-end justify-between h-16 gap-1">
                                        <div class="w-4 bg-orange-500/30 rounded-t" style="height: 20%"></div>
                                        <div class="w-4 bg-orange-500/40 rounded-t" style="height: 40%"></div>
                                        <div class="w-4 bg-orange-500/50 rounded-t" style="height: 55%"></div>
                                        <div class="w-4 bg-orange-500/60 rounded-t" style="height: 80%"></div>
                                        <div class="w-4 bg-orange-500/70 rounded-t" style="height: 100%"></div>
                                        <div class="w-4 bg-orange-500/75 rounded-t" style="height: 90%"></div>
                                        <div class="w-4 bg-orange-500/80 rounded-t" style="height: 95%"></div>
                                        <div class="w-4 bg-orange-500/80 rounded-t" style="height: 85%"></div>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 text-center">Impressions over time</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Transparent Pricing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                No Hidden Costs
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Transparent pricing</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">See exactly what you pay before you launch. Your ad budget and the service fee are shown upfront. No surprise charges, no minimum commitments.</p>
                        </div>
                        <div>
                            <!-- Pricing breakdown mockup -->
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Ad budget</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">$75.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Service fee</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">$15.00</span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-white/10 pt-3 flex justify-between">
                                        <span class="font-bold text-gray-900 dark:text-white">Total</span>
                                        <span class="font-bold text-orange-600 dark:text-orange-400">$90.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Advanced Mode -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Pro Controls
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Advanced mode</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Want more control? Choose a campaign objective (awareness, traffic, or engagement), set daily or lifetime budgets, write custom ad copy, fine-tune targeting, and pick specific placements like Facebook Feed, Instagram Feed, Stories, or Reels.</p>

                    <div class="flex items-center justify-center gap-4">
                        <div class="flex gap-2">
                            <div class="w-8 h-8 rounded-full bg-orange-500 border-2 border-white dark:border-white/20 shadow flex items-center justify-center">
                                <svg aria-hidden="true" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-amber-500 border-2 border-white dark:border-white/20 shadow flex items-center justify-center">
                                <svg aria-hidden="true" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-white dark:border-white/20 shadow flex items-center justify-center">
                                <svg aria-hidden="true" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-orange-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-amber-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Newsletters -->
                <a href="{{ marketing_url('/features/newsletters') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Newsletters</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Send branded emails to your followers and ticket buyers with a drag-and-drop editor.</p>
                        <span class="inline-flex items-center text-sky-500 dark:text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Analytics -->
                <a href="{{ marketing_url('/features/analytics') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-3xl border border-emerald-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors">Analytics</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Track views, followers, and engagement across all your events and schedules.</p>
                        <span class="inline-flex items-center text-emerald-500 dark:text-emerald-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-orange-500/10 border border-orange-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-orange-300 dark:hover:border-orange-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-musicians') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-orange-300 dark:hover:border-orange-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Musicians</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-bars') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-orange-300 dark:hover:border-orange-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Bars</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
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
                    Everything you need to know about Boost.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-2xl border border-orange-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Does Boost guarantee ticket sales or attendance?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            No. Boost increases your event's visibility by placing ads in front of relevant audiences on Facebook and Instagram. Results depend on your event, audience, creative, and budget. Think of it as distribution, not a guarantee.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-2xl border border-orange-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How much does Boost cost?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            You set your own ad budget from $10 to $1,000. Event Schedule charges a transparent service fee on top of your ad spend. The full breakdown is shown before you launch. No hidden costs, no minimum commitments.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Which platforms do Boost ads run on?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Boost ads run on Facebook and Instagram, including feeds, Stories, and Reels placements. Your ad is automatically formatted for each placement.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How does targeting work?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Boost uses your event's location, category, and details to build a relevant audience automatically. You can also customize age range, interests, and radius. Advanced mode lets you fine-tune every parameter.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-2xl border border-orange-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I pause or cancel a campaign?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 5 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 5" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. You can pause or cancel a running campaign at any time from your dashboard. If you cancel before any budget is spent, you receive a full refund. If Meta rejects the ad, you also receive a full refund. When a campaign completes, any unspent budget and the proportional service fee are automatically refunded.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 6 ? null : 6" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What happens to unspent budget?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 6 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 6" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            If Meta rejects your ad or you cancel before any budget is spent, you receive a full refund. When a campaign completes with unspent budget, the remaining ad spend and the proportional service fee are automatically refunded. You receive email notifications when your campaign is created, when 75% of your budget has been spent, and when it completes with final stats.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-2xl border border-orange-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 7 ? null : 7" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Do I need a Meta (Facebook) Ads account?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 7 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 7" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            No. Event Schedule handles the ad account, creative, and delivery on your behalf. You do not need to create or manage a Meta Ads account.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-orange-600 to-amber-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Put your next event in front of the right audience
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Launch a campaign in minutes. No ad experience needed.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-orange-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule Boost",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Advertising Software",
        "operatingSystem": "Web",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Ad budget starts at $10"
        },
        "featureList": [
            "Automated Facebook and Instagram ads",
            "Smart audience targeting",
            "Budget control from $10 to $1,000",
            "Real-time campaign analytics",
            "Transparent pricing",
            "Advanced targeting mode"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
