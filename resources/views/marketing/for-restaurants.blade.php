<x-marketing-layout>
    <x-slot name="title">Event Schedule for Restaurants | Turn First-Time Diners into Regulars</x-slot>
    <x-slot name="description">Email your regulars directly and fill every seat. Announce seasonal menus, sell tickets to wine dinners and prix fixe events, and reach your regulars directly. No algorithm. Free forever.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Restaurants</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Restaurants",
        "description": "Announce seasonal menus, sell tickets to wine dinners and prix fixe events, and reach your regulars directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Restaurants"
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
                "name": "What kinds of events can restaurants list?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Anything that brings guests through the door. Wine tastings, chef's table dinners, live music nights, brunch specials, holiday menus, cooking classes, tasting menus, or seasonal pop-ups. If it's happening at your restaurant, it belongs on your calendar."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to special dining events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Sell tickets for wine dinners, chef's tables, tasting events, and any ticketed experience. Connect Stripe and guests can purchase directly from your calendar. Every ticket includes a QR code, and Event Schedule charges zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "How do guests find out about upcoming events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Guests can follow your restaurant's schedule and get email notifications when you add new events. You can also send newsletters directly to followers with your upcoming lineup, and embed the calendar on your restaurant's website."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for restaurants?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Creating your event calendar, sharing it, and building a following are all free forever. Ticketing, newsletters, and advanced features are available on the Pro plan, with zero platform fees on any ticket sales."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with warm burgundy tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-rose-700/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-red-900/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <!-- Subtle gold accent -->
            <div class="absolute top-40 right-1/3 w-[200px] h-[200px] bg-amber-500/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Floating Prix Fixe Menu Card - UNIQUE TO RESTAURANTS -->
        <div class="absolute top-16 right-8 md:right-20 lg:right-32 hidden sm:block">
            <div class="relative animate-menu-float">
                <!-- Menu card -->
                <div class="w-48 md:w-56 bg-gradient-to-b from-amber-50 to-amber-100/95 rounded-lg shadow-2xl shadow-black/30 p-5 border border-amber-200/50 transform rotate-3 hover:rotate-0 transition-transform duration-500">
                    <!-- Elegant header -->
                    <div class="text-center border-b border-rose-800/20 pb-3 mb-4">
                        <div class="text-rose-900 text-[10px] tracking-[0.3em] uppercase">Tasting Menu</div>
                        <div class="text-rose-800 font-serif text-lg font-semibold mt-1">Chef's Selection</div>
                    </div>
                    <!-- Menu courses -->
                    <div class="space-y-3 text-rose-900/80">
                        <div class="text-center">
                            <div class="text-[9px] tracking-wider uppercase text-rose-600">First</div>
                            <div class="text-xs font-medium">Burrata & Heirloom Tomato</div>
                            <div class="text-[9px] italic text-amber-700 mt-0.5">Prosecco</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[9px] tracking-wider uppercase text-rose-600">Second</div>
                            <div class="text-xs font-medium">Seared Scallops</div>
                            <div class="text-[9px] italic text-amber-700 mt-0.5">Chardonnay</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[9px] tracking-wider uppercase text-rose-600">Main</div>
                            <div class="text-xs font-medium">Wagyu Beef Tenderloin</div>
                            <div class="text-[9px] italic text-amber-700 mt-0.5">Cabernet Sauvignon</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[9px] tracking-wider uppercase text-rose-600">Dessert</div>
                            <div class="text-xs font-medium">Chocolate Souffl&eacute;</div>
                            <div class="text-[9px] italic text-amber-700 mt-0.5">Sauternes</div>
                        </div>
                    </div>
                    <!-- Price footer -->
                    <div class="text-center mt-4 pt-3 border-t border-rose-800/20">
                        <div class="text-rose-800 font-semibold text-sm">$145 <span class="text-[10px] font-normal">per guest</span></div>
                        <div class="text-[9px] text-rose-600 mt-1">Wine pairings included</div>
                    </div>
                </div>
                <!-- Decorative shadow/glow -->
                <div class="absolute -inset-4 bg-amber-300/10 rounded-2xl blur-xl -z-10"></div>
            </div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Restaurants & Dining Experiences</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Turn first-time diners<br>
                <span class="text-gradient-burgundy">into regulars.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Stop paying Facebook to reach people who already love your food. Email your regulars directly, announce your seasonal menus, and fill every seat at your next wine dinner.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-700 to-red-900 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-rose-500/25">
                    Create your restaurant's calendar
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Restaurant type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-500/30">Fine Dining</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-500/30">Wine Dinners</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-xs font-medium border border-red-200 dark:border-red-500/30">Chef's Table</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-200 dark:border-orange-500/30">Tasting Menus</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-200 dark:border-yellow-500/30">Brunch</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-500/30">Private Events</span>
            </div>
        </div>
    </section>

    <!-- The Dining Year Section - UNIQUE TO RESTAURANTS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    The dining year, planned
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Restaurants run on seasons and occasions. Valentine's Day, Mother's Day, harvest menus, NYE galas - set up your annual calendar once, remind your fans every year.
                </p>
            </div>

            <!-- Seasonal Calendar Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Winter/Early Spring (Jan-Mar) -->
                <div class="bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900/40 dark:to-cyan-900/40 rounded-2xl border border-rose-200 dark:border-rose-500/20 p-5 relative overflow-hidden group hover:border-rose-300 dark:hover:border-rose-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-rose-300 dark:text-rose-400/30 text-4xl">&#10084;</div>
                    <div class="text-rose-600 dark:text-rose-300 text-xs font-semibold tracking-wider uppercase mb-3">Jan - Mar</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Valentine's Prix Fixe</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Winter Wine Dinners</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Truffle Season</span>
                        </div>
                    </div>
                </div>

                <!-- Spring (Apr-Jun) -->
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/40 dark:to-teal-900/40 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 p-5 relative overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-emerald-300 dark:text-emerald-400/30 text-4xl">&#127799;</div>
                    <div class="text-emerald-600 dark:text-emerald-300 text-xs font-semibold tracking-wider uppercase mb-3">Apr - Jun</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Mother's Day Brunch</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Spring Menu Launch</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Graduation Dinners</span>
                        </div>
                    </div>
                </div>

                <!-- Summer (Jul-Sep) -->
                <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/40 dark:to-orange-900/40 rounded-2xl border border-amber-200 dark:border-amber-500/20 p-5 relative overflow-hidden group hover:border-amber-300 dark:hover:border-amber-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-amber-300 dark:text-amber-400/30 text-4xl">&#9728;</div>
                    <div class="text-amber-600 dark:text-amber-300 text-xs font-semibold tracking-wider uppercase mb-3">Jul - Sep</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Patio Season Opens</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Al Fresco Wine Nights</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Summer Tasting Menu</span>
                        </div>
                    </div>
                </div>

                <!-- Fall/Winter (Oct-Dec) -->
                <div class="bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-yellow-900/40 dark:to-amber-900/40 rounded-2xl border border-yellow-200 dark:border-yellow-500/20 p-5 relative overflow-hidden group hover:border-yellow-300 dark:hover:border-yellow-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-yellow-300 dark:text-yellow-400/30 text-4xl">&#127810;</div>
                    <div class="text-yellow-600 dark:text-yellow-300 text-xs font-semibold tracking-wider uppercase mb-3">Oct - Dec</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Harvest Menu</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Thanksgiving Feast</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">NYE Champagne Gala</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recurring specials note -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                    <svg aria-hidden="true" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-gray-400 text-sm">Plus weekly recurring events: Wine Wednesday, Taco Tuesday, Sunday Brunch</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Seasonal Menu Announcements - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-red-100 dark:from-rose-900 dark:to-red-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">New fall menu? Your fans are first to know.</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">Seasonal menu launches deserve an audience. One click emails everyone who signed up - no algorithm decides who sees your new dishes.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your diners, direct reach</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No middleman</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-rose-100 dark:bg-gradient-to-br dark:from-rose-500/20 dark:to-red-500/20 rounded-2xl border border-rose-200 dark:border-rose-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-red-600 rounded-xl flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">Fall Harvest Menu Launch</div>
                                            <div class="text-rose-600 dark:text-rose-300 text-xs">Sending to 1,847 followers...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Butternut Squash Bisque</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Braised Short Rib</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Apple Tarte Tatin</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prix Fixe Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Wine dinners that sell out</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">5-course tastings, chef's tables, pairing dinners. Sell tickets, manage capacity, scan at the door.</p>

                    <!-- Elegant ticket card visual -->
                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-amber-100 to-amber-50 rounded-xl border border-amber-300/50 p-4 w-44 shadow-lg transform -rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-amber-800 text-[10px] tracking-widest uppercase">Tasting Menu</div>
                                <div class="text-amber-900 text-sm font-serif font-semibold mt-1">5-Course Pairing</div>
                                <div class="text-amber-700 text-xl font-bold mt-2">$125<span class="text-xs font-normal">pp</span></div>
                                <div class="text-amber-600 text-[10px] mt-1">Sat, Nov 15 &bull; 7:30 PM</div>
                                <div class="text-amber-500 text-[9px] mt-1">Only 4 seats left</div>
                                <div class="mt-3 w-12 h-12 bg-amber-900/10 rounded-lg mx-auto p-1.5">
                                    <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%2392400e%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Occasion Packages -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-rose-100 dark:from-cyan-900 dark:to-rose-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Special Occasions
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Valentine's. Mother's Day. Covered.</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Set up annual events once. Get reminded when it's time to promote next year.</p>

                    <!-- Holiday calendar visual -->
                    <div class="grid grid-cols-4 gap-2">
                        <div class="text-center p-2 rounded-lg bg-rose-100 dark:bg-rose-500/20 border border-rose-200 dark:border-rose-500/30">
                            <div class="text-rose-500 dark:text-rose-400 text-lg">&#10084;</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[9px]">V-Day</div>
                        </div>
                        <div class="text-center p-2 rounded-lg bg-cyan-100 dark:bg-cyan-500/20 border border-cyan-200 dark:border-cyan-500/30">
                            <div class="text-cyan-500 dark:text-cyan-400 text-lg">&#127800;</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[9px]">Mom</div>
                        </div>
                        <div class="text-center p-2 rounded-lg bg-orange-100 dark:bg-orange-500/20 border border-orange-200 dark:border-orange-500/30">
                            <div class="text-orange-500 dark:text-orange-400 text-lg">&#127810;</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[9px]">T-giving</div>
                        </div>
                        <div class="text-center p-2 rounded-lg bg-amber-100 dark:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30">
                            <div class="text-amber-500 dark:text-amber-400 text-lg">&#127878;</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[9px]">NYE</div>
                        </div>
                    </div>
                </div>

                <!-- Private Dining Inquiries (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Booking Inbox
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Private dining inquiries come to you</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Corporate dinners, birthday celebrations, holiday buyouts. They submit the request, you approve. No back-and-forth emails.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Corporate events</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Buyouts</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Birthday dinners</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Private Dining Requests</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-100 dark:bg-sky-500/20 border border-sky-200 dark:border-sky-400/30">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">EB</div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white text-sm font-medium">Emily B. - Company Dinner</div>
                                        <div class="text-sky-600 dark:text-sky-300 text-xs">Dec 15 &bull; Party of 24 &bull; ~$3,500</div>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">MK</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Michael K. - 50th Birthday</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">Jan 8 &bull; Party of 16</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Dining Spaces -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Spaces
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Main room. Private dining. Patio.</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Separate calendars for each space. Guests find the right room, you avoid overbooking.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Main Dining</span>
                            <span class="ml-auto text-emerald-600 dark:text-emerald-300 text-xs">8 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Private Room</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">4 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Patio</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">3 events</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code - Menu & Check-in -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        QR Codes
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Scan at the table, scan at the door</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">QR codes for digital menus and event check-in. One scan, they follow your schedule.</p>

                    <!-- Dual QR visual -->
                    <div class="flex justify-center gap-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white rounded-lg p-2 mb-2 shadow-sm">
                                <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%230f766e%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            </div>
                            <div class="text-cyan-600 dark:text-cyan-300 text-[10px] font-medium">Menu</div>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white rounded-lg p-2 mb-2 shadow-sm">
                                <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%230f766e%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            </div>
                            <div class="text-cyan-600 dark:text-cyan-300 text-[10px] font-medium">Check-in</div>
                        </div>
                    </div>
                </div>

                <!-- Event Photos for Instagram - BOTTOM RIGHT -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ready for Instagram</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Auto-generate promo graphics for your events. Download and post in seconds.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-sky-100 dark:bg-gradient-to-br dark:from-sky-500/30 dark:to-cyan-500/30 rounded-xl border border-sky-200 dark:border-sky-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-rose-600/40 to-amber-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-white text-[10px] font-semibold mb-1">THIS FRIDAY</div>
                                <div class="text-sky-200 text-xs font-bold">Harvest Dinner</div>
                                <div class="text-gray-300 dark:text-gray-400 text-[8px] mt-1">5 courses + wine</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-sky-500 rounded-full flex items-center justify-center">
                                <svg aria-hidden="true" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Customer Journey Section - From First Visit to Regular -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From first visit to regular
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Every diner can become a fan. Here's how Event Schedule helps you build lasting relationships.
                </p>
            </div>

            <!-- Journey visualization -->
            <div class="relative">
                <!-- Connection line (desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-rose-500/50 via-amber-500/50 to-emerald-500/50 -translate-y-1/2 z-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 relative z-10">
                    <!-- Step 1: Discovery -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-rose-100 dark:bg-gradient-to-br dark:from-rose-500/30 dark:to-red-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-rose-200 dark:border-rose-500/30">
                            <svg aria-hidden="true" class="w-7 h-7 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h4 class="text-gray-900 dark:text-white font-semibold mb-2">Discovery</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Guest finds you online or walks in</p>
                    </div>

                    <!-- Step 2: Follow -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-orange-100 dark:bg-gradient-to-br dark:from-orange-500/30 dark:to-amber-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-orange-200 dark:border-orange-500/30">
                            <svg aria-hidden="true" class="w-7 h-7 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h4 class="text-gray-900 dark:text-white font-semibold mb-2">Follow</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Signs up for your updates via QR or calendar</p>
                    </div>

                    <!-- Step 3: Return -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-amber-100 dark:bg-gradient-to-br dark:from-amber-500/30 dark:to-yellow-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-200 dark:border-amber-500/30">
                            <svg aria-hidden="true" class="w-7 h-7 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-gray-900 dark:text-white font-semibold mb-2">Return</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Gets email about new menu, comes back</p>
                    </div>

                    <!-- Step 4: Regular -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-emerald-100 dark:bg-gradient-to-br dark:from-emerald-500/30 dark:to-teal-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-200 dark:border-emerald-500/30">
                            <svg aria-hidden="true" class="w-7 h-7 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h4 class="text-gray-900 dark:text-white font-semibold mb-2">Regular</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Books chef's table, buys wine dinner tickets</p>
                    </div>

                    <!-- Step 5: Advocate -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-gradient-to-br dark:from-blue-500/30 dark:to-blue-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-blue-200 dark:border-blue-500/30">
                            <svg aria-hidden="true" class="w-7 h-7 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                        <h4 class="text-gray-900 dark:text-white font-semibold mb-2">Advocate</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Shares your events with friends</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Virtual Cooking Classes Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Teach the world your secrets</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Virtual cooking classes. Live wine tastings. Fans anywhere can join, pay, and cook along.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Cooking classes</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Wine tastings</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Global reach</span>
                            </div>
                            <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-white dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Virtual Class</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        <span class="text-red-400 text-[10px]">LIVE</span>
                                    </div>
                                </div>
                                <div class="bg-sky-100 dark:bg-gradient-to-br dark:from-sky-600/30 dark:to-blue-600/30 rounded-lg p-4 text-center mb-3">
                                    <div class="text-2xl mb-1">&#127859;</div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Pasta Making</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">with Chef Marco</div>
                                </div>
                                <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>47 viewers</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of restaurants
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From fine dining to casual bistros, Event Schedule fits your style.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Fine Dining Restaurants -->
                <x-sub-audience-card
                    name="Fine Dining Restaurants"
                    description="Tasting menus, wine pairings, and special occasion dinners. Build anticipation for your culinary experiences."
                    icon-color="rose"
                    blog-slug="for-fine-dining-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Wine Bars & Tapas -->
                <x-sub-audience-card
                    name="Wine Bars & Tapas"
                    description="Tastings, flights, and small plate specials. Attract wine enthusiasts and foodies alike."
                    icon-color="amber"
                    blog-slug="for-wine-bars-tapas"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Farm-to-Table Restaurants -->
                <x-sub-audience-card
                    name="Farm-to-Table Restaurants"
                    description="Seasonal menus, producer dinners, and harvest events. Connect guests with your local sources."
                    icon-color="emerald"
                    blog-slug="for-farm-to-table-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Supper Clubs & Private Dining -->
                <x-sub-audience-card
                    name="Supper Clubs & Private Dining"
                    description="Intimate gatherings and exclusive experiences. Manage limited seating and create anticipation."
                    icon-color="violet"
                    blog-slug="for-supper-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Casual Dining & Bistros -->
                <x-sub-audience-card
                    name="Casual Dining & Bistros"
                    description="Weekly specials, happy hours, and themed nights. Keep your regulars coming back."
                    icon-color="orange"
                    blog-slug="for-casual-dining-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Chef's Tables & Pop-ups -->
                <x-sub-audience-card
                    name="Chef's Tables & Pop-ups"
                    description="Limited seating, unique experiences, and collaborations. Create buzz for your culinary events."
                    icon-color="pink"
                    blog-slug="for-chefs-tables"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
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
                    name="Sub-Schedules"
                    description="Organize events into categories and groups"
                    :url="marketing_url('/features/sub-schedules')"
                    icon-color="rose"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
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
                <a href="{{ marketing_url('/for-bars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Bars</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-hotels-and-resorts') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Hotels & Resorts</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-breweries-and-wineries') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Breweries & Wineries</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-food-trucks-and-vendors') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Food Trucks & Vendors</div>
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
                    Everything restaurant owners ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 rounded-2xl border border-green-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What kinds of events can restaurants list?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Anything that brings guests through the door. Wine tastings, chef's table dinners, live music nights, brunch specials, holiday menus, cooking classes, tasting menus, or seasonal pop-ups. If it's happening at your restaurant, it belongs on your calendar.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I sell tickets to special dining events?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Sell tickets for wine dinners, chef's tables, tasting events, and any ticketed experience. Connect Stripe and guests can purchase directly from your calendar. Every ticket includes a QR code, and Event Schedule charges zero platform fees.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-green-100 dark:from-teal-900 dark:to-green-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do guests find out about upcoming events?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Guests can follow your restaurant's schedule and get email notifications when you add new events. You can also send newsletters directly to followers with your upcoming lineup, and embed the calendar on your restaurant's website.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for restaurants?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Creating your event calendar, sharing it, and building a following are all free forever. Ticketing, newsletters, and advanced features are available on the Pro plan, with zero platform fees on any ticket sales.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-rose-700 to-red-900 py-24 overflow-hidden">
        <!-- Subtle tablecloth texture overlay -->
        <div class="absolute inset-0 grid-overlay"></div>
        <!-- Gold accent glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-amber-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop paying to reach your own fans
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your regulars directly. Fill every seat. Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-rose-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
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
        "name": "Event Schedule for Restaurants",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Restaurant Event Management Software",
        "operatingSystem": "Web",
        "description": "Email your regulars directly and fill every seat. Announce seasonal menus, sell tickets to wine dinners and prix fixe events, and reach your regulars directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Seasonal menu announcement newsletters",
            "Prix fixe and wine dinner ticketing",
            "Annual dining calendar with holiday events",
            "Private dining inquiry management",
            "Multiple dining space calendars",
            "QR codes for menus and check-in",
            "Virtual cooking class support",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        .text-gradient-burgundy {
            background: linear-gradient(135deg, #be123c, #7f1d1d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient-burgundy {
            background: linear-gradient(135deg, #fb7185, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Floating menu card animation */
        @keyframes menu-float {
            0%, 100% {
                transform: translateY(0px) rotate(3deg);
            }
            50% {
                transform: translateY(-10px) rotate(3deg);
            }
        }

        .animate-menu-float {
            animation: menu-float 4s ease-in-out infinite;
        }
    </style>
</x-marketing-layout>
