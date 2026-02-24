<x-marketing-layout>
    <x-slot name="title">Event Schedule for Comedians | Track Your Spots, Fill Your Shows</x-slot>
    <x-slot name="description">One link for every set. Track your mics, guest spots, and headlines. Email fans directly - algorithms can't bury it. Zero fees when you sell tickets. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Comedians",
        "description": "Track your mics, guest spots, and headlines. Email fans directly. Zero fees when you sell tickets. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Comedians"
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
                "name": "Is Event Schedule free for comedians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show dates and building a fan following. Ticketing and newsletters are available on the Pro and Enterprise plans, with zero platform fees on any ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my comedy shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Create multiple ticket types like general admission, VIP, and early bird. Every ticket includes a QR code for check-in at the door. Zero platform fees - you only pay Stripe's standard processing."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans know when I have a show near them?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans follow your schedule and get notified when you add new shows. You can also send newsletters directly to your followers with upcoming dates. Share your schedule link in your social bios, on podcasts, or anywhere fans find you."
                }
            },
            {
                "@type": "Question",
                "name": "Can comedy clubs add me to their lineup?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When a comedy club adds you to their event on Event Schedule, the show automatically appears on your schedule too. No need to add the same gig in two places. Both calendars stay in sync so your fans always see your latest bookings."
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
        "name": "Event Schedule for Comedians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Comedy Show Scheduling Software",
        "operatingSystem": "Web",
        "description": "Track your open mics, guest spots, and headlining gigs. Email fans directly - no algorithm burying your posts. Zero fees on ticket sales. Built for stand-up comics.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Open mic tracking",
            "Guest spot management",
            "Direct fan newsletters",
            "Zero-fee ticketing",
            "Late night show support",
            "Comedy club integration",
            "Tour date management",
            "Promo graphic generator"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Comedy Club Stage Vibe -->
    <section class="relative bg-white dark:bg-[#0f0808] py-32 overflow-hidden">
        <!-- Brick wall texture overlay -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg aria-hidden="true" xmlns=\"http://www.w3.org/2000/svg\" width=\"60\" height=\"30\"><rect width=\"60\" height=\"30\" fill=\"none\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"30\" y1=\"0\" x2=\"30\" y2=\"15\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"0\" y1=\"15\" x2=\"60\" y2=\"15\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"15\" y1=\"15\" x2=\"15\" y2=\"30\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"45\" y1=\"15\" x2=\"45\" y2=\"30\" stroke=\"%23fff\" stroke-width=\"0.5\"/></svg>');"></div>

        <!-- Spotlight effect from above -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px]">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-4 h-4 bg-amber-200 rounded-full blur-sm"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[300px] border-r-[300px] border-t-[500px] border-l-transparent border-r-transparent border-t-amber-500/[0.03]"></div>
        </div>

        <!-- Neon glow accents -->
        <div class="absolute top-40 left-10 w-32 h-1 bg-gradient-to-r from-red-500/50 to-transparent blur-sm animate-pulse"></div>
        <div class="absolute top-60 right-10 w-24 h-1 bg-gradient-to-l from-amber-500/50 to-transparent blur-sm animate-pulse" style="animation-delay: 0.5s;"></div>
        <div class="absolute bottom-40 left-20 w-20 h-1 bg-gradient-to-r from-rose-500/50 to-transparent blur-sm animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Microphone icon badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-amber-100 dark:bg-[#0f0f14] border border-amber-300 dark:border-amber-500/30 mb-8 backdrop-blur-sm animate-reveal" style="opacity: 0;">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </div>
                <span class="text-sm text-amber-700 dark:text-amber-200/90 font-medium tracking-wide">Built for Comics</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                The grind is real.<br>
                <span class="neon-text">One link for every set.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Open mic Monday. Barking Tuesday. Guest set Wednesday. Headlining Friday. One link shows fans every set - yours, not the algorithm's.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Get your link
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The journey badges -->
            <div class="mt-14 flex flex-wrap justify-center items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 text-xs font-medium border border-red-300 dark:border-red-800/50">Open Mic</span>
                <svg aria-hidden="true" class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium border border-amber-300 dark:border-amber-800/50">Bringer</span>
                <svg aria-hidden="true" class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 text-xs font-medium border border-orange-300 dark:border-orange-800/50">Guest Spot</span>
                <svg aria-hidden="true" class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-xs font-medium border border-rose-300 dark:border-rose-800/50">Feature</span>
                <svg aria-hidden="true" class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="inline-flex items-center px-4 py-1.5 rounded-lg bg-gradient-to-r from-amber-600/60 to-rose-600/60 text-white text-xs font-bold border border-amber-500/50 shadow-lg shadow-amber-500/20">Headliner</span>
            </div>
        </div>
    </section>

    <!-- The Problem Section - Unique to Comedy -->
    <section class="bg-white dark:bg-[#0f0808] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-red-400 mb-2">7</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">mics a week just to stay sharp</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-amber-400 mb-2">5</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">clubs where you're trying to get regular</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-rose-400 mb-2">~3%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of your followers actually see your show posts</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features - Comedy Club Style Cards -->
    <section class="bg-gray-50 dark:bg-[#0a0606] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Main Feature: The Weekly Grind Tracker -->
            <div class="relative rounded-3xl bg-gradient-to-br from-red-100 to-red-50 dark:from-[#1a1010] dark:to-[#0f0808] border border-red-200 dark:border-red-900/30 p-8 lg:p-12 mb-8 overflow-hidden">
                <!-- Subtle stage light glow -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-[100px]"></div>

                <div class="relative grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-sm font-medium mb-6 border border-red-200 dark:border-red-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            Your Weekly Lineup
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-6">See your whole week.<br>Never double-book again.</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Running between clubs? Texting yourself set times? One calendar shows every mic, every guest set, every headline - plus your total stage time for the week.</p>
                        <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                            <li class="flex items-center gap-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Track set lengths (tight 5, 10, 15, feature, headline)
                            </li>
                            <li class="flex items-center gap-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                See your weekly stage time at a glance
                            </li>
                            <li class="flex items-center gap-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Syncs with Google Calendar (both ways)
                            </li>
                        </ul>
                    </div>

                    <!-- Visual: Weekly Schedule Mock -->
                    <div class="relative">
                        <div class="bg-gray-100 dark:bg-black/60 rounded-2xl border border-gray-200 dark:border-white/10 p-5 backdrop-blur-sm">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">This Week</span>
                                <span class="text-amber-600 dark:text-amber-400 text-xs font-semibold">47 min stage time</span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-4 p-3 rounded-xl bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30">
                                    <div class="w-12 text-center">
                                        <div class="text-red-600 dark:text-red-300 text-xs font-bold">MON</div>
                                        <div class="text-gray-900 dark:text-white text-lg font-bold">12</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white font-semibold">Stand Up NY</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-sm">Open mic · 7 PM signup</div>
                                    </div>
                                    <div class="inline-flex items-center px-2 py-1 rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs font-medium">5 min</div>
                                </div>
                                <div class="flex items-center gap-4 p-3 rounded-xl bg-amber-100 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30">
                                    <div class="w-12 text-center">
                                        <div class="text-amber-600 dark:text-amber-300 text-xs font-bold">WED</div>
                                        <div class="text-gray-900 dark:text-white text-lg font-bold">14</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white font-semibold">Comedy Cellar</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-sm">Guest set · 9:30 PM</div>
                                    </div>
                                    <div class="inline-flex items-center px-2 py-1 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-medium">12 min</div>
                                </div>
                                <div class="flex items-center gap-4 p-3 rounded-xl bg-orange-100 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800/30">
                                    <div class="w-12 text-center">
                                        <div class="text-orange-600 dark:text-orange-300 text-xs font-bold">THU</div>
                                        <div class="text-gray-900 dark:text-white text-lg font-bold">15</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white font-semibold">Gotham Comedy</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-sm">Late show · 11 PM</div>
                                    </div>
                                    <div class="inline-flex items-center px-2 py-1 rounded bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 text-xs font-medium">10 min</div>
                                </div>
                                <div class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-rose-100 to-amber-100 dark:from-rose-900/30 dark:to-amber-900/30 border border-rose-200 dark:border-rose-700/40">
                                    <div class="w-12 text-center">
                                        <div class="text-rose-600 dark:text-rose-300 text-xs font-bold">SAT</div>
                                        <div class="text-gray-900 dark:text-white text-lg font-bold">17</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white font-semibold">Carolines</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-sm">Two shows: 8 PM & 10:30 PM</div>
                                    </div>
                                    <div class="inline-flex items-center px-2 py-1 rounded bg-gradient-to-r from-rose-600/60 to-amber-600/60 text-white text-xs font-bold">Headlining</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Features -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <!-- Newsletter Feature -->
                <div class="relative rounded-3xl bg-gradient-to-br from-rose-100 to-rose-50 dark:from-[#1a0f10] dark:to-[#0f0808] border border-rose-200 dark:border-rose-900/30 p-8 overflow-hidden">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-rose-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-sm font-medium mb-5 border border-rose-200 dark:border-rose-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Direct to Fans
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Instagram buried your post.<br>Email won't.</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">You posted about your show. 3% of your followers saw it. The rest saw ads. With email, everyone who signed up actually gets notified. No algorithm deciding who deserves to see it.</p>

                        <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-full bg-rose-500/20 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Headlining Saturday!</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">Sent to 1,247 fans</div>
                                </div>
                            </div>
                            <div class="flex gap-4 text-xs">
                                <div class="text-gray-400"><span class="text-emerald-400 font-semibold">68%</span> opened</div>
                                <div class="text-gray-400"><span class="text-amber-400 font-semibold">23%</span> clicked</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Sales Feature -->
                <div class="relative rounded-3xl bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-[#101a0f] dark:to-[#0f0808] border border-emerald-200 dark:border-emerald-900/30 p-8 overflow-hidden">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Sell Tickets
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Your show, your money.<br>Zero platform fees.</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Producing your own show? Sell tickets directly. Money goes straight to your Stripe - we don't take a cut. Your hustle, your earnings.</p>

                        <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-gray-400 text-sm">Saturday Late Show</span>
                                <span class="text-emerald-400 text-sm font-semibold">73 sold</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <span class="text-gray-900 dark:text-white text-sm">General Admission</span>
                                    <span class="text-gray-900 dark:text-white font-medium">$20</span>
                                </div>
                                <div class="flex justify-between items-center p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                    <span class="text-gray-900 dark:text-white text-sm">Front Row + Meet & Greet</span>
                                    <span class="text-gray-900 dark:text-white font-medium">$50</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between">
                                <span class="text-gray-400 text-sm">Platform fee</span>
                                <span class="text-emerald-400 font-bold">$0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Three Column Features -->
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Late Night Shows -->
                <div class="rounded-2xl bg-gradient-to-br from-blue-100 to-blue-50 dark:from-[#12101a] dark:to-[#0f0808] border border-blue-200 dark:border-blue-900/30 p-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-4">
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Built for late nights</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">10:30 show? Midnight mic that runs until 2? We get it. No more calendar apps showing tomorrow's date for tonight's set.</p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/30">
                        <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-blue-600 dark:text-blue-300 text-sm font-medium">10:30 PM - 12:30 AM</span>
                    </div>
                </div>

                <!-- Club Links -->
                <div class="rounded-2xl bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0f0808] border border-amber-200 dark:border-amber-900/30 p-6">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-4">
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Booked by a club? It shows up automatically.</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">When a club adds you to their lineup, your schedule updates. No copy-paste. No 'wait, what time did they say?'</p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-700 dark:text-amber-300 text-[10px] font-bold">CC</div>
                        <div class="w-6 h-6 rounded bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-700 dark:text-rose-300 text-[10px] font-bold">GC</div>
                        <div class="w-6 h-6 rounded bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-700 dark:text-red-300 text-[10px] font-bold">SU</div>
                        <span class="text-gray-500 text-xs ml-1">+ more</span>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="rounded-2xl bg-gradient-to-br from-orange-100 to-orange-50 dark:from-[#1a100f] dark:to-[#0f0808] border border-orange-200 dark:border-orange-900/30 p-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center mb-4">
                        <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Show graphics in one click</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Download Instagram-ready flyers for any set. Stop begging your friend who 'knows Canva.'</p>
                    <div class="flex justify-center">
                        <div class="w-16 h-20 rounded-lg bg-gradient-to-br from-orange-600/40 to-rose-600/40 border border-orange-500/30 flex items-center justify-center">
                            <svg aria-hidden="true" class="w-6 h-6 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Journey Section -->
    <section class="bg-white dark:bg-[#0f0808] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From open mic to headliner
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your career
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Open Mic Comics -->
                <div class="bg-gradient-to-br from-red-100 to-red-50 dark:from-[#1a0f0f] dark:to-[#0f0808] rounded-2xl p-6 border border-red-200 dark:border-red-900/20 hover:border-red-300 dark:hover:border-red-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grinding the mics</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Track your spots across every open mic in the city. Know where you're signed up tonight.</p>
                </div>

                <!-- Working Comics -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0f0808] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Getting regular</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Guest spots coming in? Track which rooms you're regular at and build your schedule.</p>
                </div>

                <!-- Feature Acts -->
                <div class="bg-gradient-to-br from-orange-100 to-orange-50 dark:from-[#1a100f] dark:to-[#0f0808] rounded-2xl p-6 border border-orange-200 dark:border-orange-900/20 hover:border-orange-300 dark:hover:border-orange-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Featuring</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">20-30 minute sets opening for headliners. Start selling tickets to your own fans.</p>
                </div>

                <!-- Headliners -->
                <div class="bg-gradient-to-br from-rose-100 to-rose-50 dark:from-[#1a0f10] dark:to-[#0f0808] rounded-2xl p-6 border border-rose-200 dark:border-rose-900/20 hover:border-rose-300 dark:hover:border-rose-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Headlining</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Your name on the marquee. Email your fans directly and sell out your shows.</p>
                </div>

                <!-- Touring Comics -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 dark:from-[#12101a] dark:to-[#0f0808] rounded-2xl p-6 border border-blue-200 dark:border-blue-900/20 hover:border-blue-300 dark:hover:border-blue-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">On the road</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Touring clubs across the country? One link shows fans in every city when you're coming through.</p>
                </div>

                <!-- Improv & Sketch -->
                <div class="bg-gradient-to-br from-teal-100 to-teal-50 dark:from-[#101510] dark:to-[#0f0808] rounded-2xl p-6 border border-teal-200 dark:border-teal-900/20 hover:border-teal-300 dark:hover:border-teal-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Improv & Sketch</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Coordinate your troupe's schedule. Everyone knows when the next Harold night is.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of comedy
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether you're doing tight fives or touring theaters, Event Schedule has you sorted.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Stand-Up Comics -->
                <x-sub-audience-card
                    name="Stand-Up Comics"
                    description="Share your sets and build a following. One link shows fans everywhere you're performing."
                    icon-color="rose"
                    blog-slug="for-stand-up-comics"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Improv Performers -->
                <x-sub-audience-card
                    name="Improv Performers"
                    description="Promote weekly shows with your troupe. Coordinate Harold nights and jam sessions."
                    icon-color="pink"
                    blog-slug="for-improv-performers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sketch Comedy Groups -->
                <x-sub-audience-card
                    name="Sketch Comedy Groups"
                    description="Coordinate ensemble schedules and share show runs. Everyone knows when the next performance is."
                    icon-color="fuchsia"
                    blog-slug="for-sketch-comedy-groups"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Open Mic Regulars -->
                <x-sub-audience-card
                    name="Open Mic Regulars"
                    description="Track spots across multiple venues. Never double-book a mic night again."
                    icon-color="purple"
                    blog-slug="for-open-mic-comics"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Touring Headliners -->
                <x-sub-audience-card
                    name="Touring Headliners"
                    description="Share tour dates with fans across the country. One link for your entire run."
                    icon-color="violet"
                    blog-slug="for-touring-comedians"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Comedy Hosts & MCs -->
                <x-sub-audience-card
                    name="Comedy Hosts & MCs"
                    description="Showcase hosting gigs and show bookers your availability. Build your reputation as the go-to host."
                    icon-color="amber"
                    blog-slug="for-comedy-podcasters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works - Simplified -->
    <section class="bg-gray-50 dark:bg-[#0a0606] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Three steps. More butts in seats.
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-red-600 to-red-700 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-red-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your sets</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Import from Google Calendar or add your mics, guest sets, and headlining gigs.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-amber-600 to-amber-700 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-amber-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share one link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Drop it in your bio. Fans see all your upcoming shows in one place.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-600 to-rose-700 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-rose-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fill the room</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Fans follow you and get notified. No more posting into the algorithm void.
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
                <x-feature-link-card
                    name="Calendar Sync"
                    description="Two-way sync with Google Calendar"
                    :url="marketing_url('/features/calendar-sync')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                <a href="{{ marketing_url('/for-musicians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Musicians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-magicians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Magicians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-spoken-word') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Spoken Word Artists</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-theater-performers') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Theater Performers</div>
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
                    Everything comedians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for comedians?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is free forever for sharing your show dates and building a fan following. Ticketing and newsletters are available on the Pro and Enterprise plans, with zero platform fees on any ticket sales.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-100 to-orange-100 dark:from-yellow-900 dark:to-orange-900 rounded-2xl border border-yellow-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I sell tickets to my comedy shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Connect your Stripe account and sell tickets directly from your schedule. Create multiple ticket types like general admission, VIP, and early bird. Every ticket includes a QR code for check-in at the door. Zero platform fees - you only pay Stripe's standard processing.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-2xl border border-orange-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do fans know when I have a show near them?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Fans follow your schedule and get notified when you add new shows. You can also send newsletters directly to your followers with upcoming dates. Share your schedule link in your social bios, on podcasts, or anywhere fans find you.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-rose-100 to-amber-100 dark:from-rose-900 dark:to-amber-900 rounded-2xl border border-rose-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can comedy clubs add me to their lineup?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. When a comedy club adds you to their event on Event Schedule, the show automatically appears on your schedule too. No need to add the same gig in two places. Both calendars stay in sync so your fans always see your latest bookings.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-gradient-to-br dark:from-[#1a0f0f] dark:to-[#0f0808] py-24 overflow-hidden border-t border-red-200 dark:border-red-900/20">
        <!-- Stage light effect (dark mode only) -->
        <div class="hidden dark:block absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px]">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[200px] border-r-[200px] border-t-[300px] border-l-transparent border-r-transparent border-t-amber-500/[0.02]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your fans want to see you.<br class="hidden md:block">Give them one link.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                No catch. No 'premium tier.'<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl hover:scale-105 transition-all shadow-xl shadow-amber-500/20">
                Get your link
                <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-gray-500 text-sm">No credit card required</p>
        </div>
    </section>


    <style {!! nonce_attr() !!}>
        .neon-text {
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 191, 36, 0.3);
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
