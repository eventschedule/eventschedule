<x-marketing-layout>
    <x-slot name="title">Event Schedule for Food Trucks | Location Sharing & Bookings</x-slot>
    <x-slot name="description">Tell customers where to find your food truck today. Share your weekly rotation, email your regulars directly, and take catering bookings with zero platform fees.</x-slot>
    <x-slot name="keywords">food truck schedule, food truck locator, mobile kitchen calendar, food truck booking, where is the food truck, food truck locations, food vendor schedule, taco truck finder, food truck events</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Food Trucks & Vendors</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Food Trucks & Vendors",
        "description": "Tell customers where to find your food truck today. Share your weekly rotation, email your regulars directly. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Food Trucks & Vendors"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Food Trucks, Vendors & Mobile Kitchens</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Tell hungry customers<br>
                <span class="text-gradient-food">where to find you</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Share your daily locations, build a following, and let regulars know where you're parking next.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-orange-600 to-amber-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-orange-500/25">
                    Create your schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Food truck type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-500/30">Food Trucks</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-500/30">Taco Trucks</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-500/30">Coffee Carts</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-xs font-medium border border-red-500/30">BBQ</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs font-medium border border-emerald-500/30">Pop-ups</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs font-medium border border-teal-500/30">Catering</span>
            </div>
        </div>
    </section>

    <!-- "The Problem" Section - UNIQUE TO FOOD TRUCKS -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20 border-b border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">Sound familiar?</h2>
            </div>

            <!-- Facebook comments mockup -->
            <div class="bg-gray-100 dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-8 max-w-lg mx-auto">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-white/10">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white font-bold text-sm">TT</div>
                    <div>
                        <div class="text-gray-900 dark:text-white font-medium text-sm">Taco Truck Tony</div>
                        <div class="text-gray-500 dark:text-gray-400 text-xs">Posted a photo</div>
                    </div>
                </div>

                <!-- Repeated comments asking where -->
                <div class="space-y-3">
                    <div class="flex gap-2 items-start">
                        <div class="w-6 h-6 rounded-full bg-blue-500/30 flex-shrink-0"></div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl px-3 py-2">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Where are you today?</span>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <div class="w-6 h-6 rounded-full bg-cyan-500/30 flex-shrink-0"></div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl px-3 py-2">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Where are you guys today??</span>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <div class="w-6 h-6 rounded-full bg-green-500/30 flex-shrink-0"></div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl px-3 py-2">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Location???</span>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <div class="w-6 h-6 rounded-full bg-blue-500/30 flex-shrink-0"></div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl px-3 py-2">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Where will you be tomorrow?</span>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <div class="w-6 h-6 rounded-full bg-yellow-500/30 flex-shrink-0"></div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl px-3 py-2">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Are you at the food park today?</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">You post once. Get asked 47 times.</p>
                <p class="text-gray-500 dark:text-gray-400">Facebook shows your posts to 3% of followers. Your regulars can't find you.</p>
                <p class="text-orange-600 dark:text-orange-400 font-medium mt-6 text-lg">There's a better way.</p>
            </div>
        </div>
    </section>

    <!-- "Your Weekly Route" Section - UNIQUE TO FOOD TRUCKS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your weekly route, always up to date
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Post your rotation once. Customers see where you are now and where you'll be next.
                </p>
            </div>

            <!-- Route visualization -->
            <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/30 dark:to-amber-900/30 rounded-3xl border border-orange-200 dark:border-white/10 p-6 md:p-8">
                <div class="flex items-center gap-2 mb-6 text-sm text-gray-500 dark:text-gray-400">
                    <svg aria-hidden="true" class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>This week's rotation</span>
                </div>

                <!-- Route stops -->
                <div class="space-y-4">
                    <!-- Monday -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 text-right">
                            <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Mon</span>
                        </div>
                        <div class="relative">
                            <div class="w-4 h-4 rounded-full bg-gray-600 border-2 border-gray-500"></div>
                            <div class="absolute top-4 left-1.5 w-0.5 h-8 bg-gray-600"></div>
                        </div>
                        <div class="flex-1 bg-gray-100 dark:bg-white/5 rounded-xl p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Downtown Food Park</span>
                            <span class="text-gray-600 dark:text-gray-300 text-xs block">11am - 2pm</span>
                        </div>
                    </div>

                    <!-- Tuesday - TODAY (highlighted) -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 text-right">
                            <span class="text-emerald-400 text-sm font-bold">TODAY</span>
                        </div>
                        <div class="relative">
                            <div class="w-4 h-4 rounded-full bg-emerald-500 border-2 border-emerald-400 animate-pulse"></div>
                            <div class="absolute top-4 left-1.5 w-0.5 h-8 bg-gradient-to-b from-emerald-500 to-orange-500"></div>
                        </div>
                        <div class="flex-1 bg-emerald-500/20 rounded-xl p-3 border border-emerald-400/30">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-emerald-300 text-sm font-medium">Tech Campus - Building A</span>
                                    <span class="text-emerald-400/70 text-xs block">11am - 2pm</span>
                                </div>
                                <div class="flex items-center gap-1 px-2 py-1 bg-emerald-500/30 rounded-full">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-emerald-300 text-xs font-medium">Now serving</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wednesday -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 text-right">
                            <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Wed</span>
                        </div>
                        <div class="relative">
                            <div class="w-4 h-4 rounded-full bg-orange-500/50 border-2 border-orange-400/50"></div>
                            <div class="absolute top-4 left-1.5 w-0.5 h-8 bg-orange-500/30"></div>
                        </div>
                        <div class="flex-1 bg-gray-100 dark:bg-white/5 rounded-xl p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Farmers Market</span>
                            <span class="text-gray-600 dark:text-gray-300 text-xs block">8am - 1pm</span>
                        </div>
                    </div>

                    <!-- Thursday -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 text-right">
                            <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Thu</span>
                        </div>
                        <div class="relative">
                            <div class="w-4 h-4 rounded-full bg-amber-500/30 border-2 border-amber-400/30"></div>
                            <div class="absolute top-4 left-1.5 w-0.5 h-8 bg-amber-500/20"></div>
                        </div>
                        <div class="flex-1 bg-gray-100 dark:bg-white/5 rounded-xl p-3 border border-dashed border-gray-200 dark:border-white/10">
                            <span class="text-gray-500 dark:text-gray-400 text-sm italic">Private event (not shown publicly)</span>
                        </div>
                    </div>

                    <!-- Friday -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 text-right">
                            <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Fri</span>
                        </div>
                        <div class="relative">
                            <div class="w-4 h-4 rounded-full bg-yellow-500/30 border-2 border-yellow-400/30"></div>
                        </div>
                        <div class="flex-1 bg-gray-100 dark:bg-white/5 rounded-xl p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Brewery District Food Truck Rally</span>
                            <span class="text-gray-600 dark:text-gray-300 text-xs block">5pm - 10pm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- "Put This On Your Truck" Section - UNIQUE TO FOOD TRUCKS -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        QR Code
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                        Put this on your truck
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">
                        Print the QR code and stick it on your window. Customers scan once, follow your truck forever. No app download needed.
                    </p>
                    <ul class="space-y-3 text-gray-500 dark:text-gray-400">
                        <li class="flex items-center gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>They follow once, never miss you again</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Build an audience that's YOURS, not Facebook's</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Get notified when they book catering</span>
                        </li>
                    </ul>
                </div>

                <!-- QR code on truck window mockup -->
                <div class="flex justify-center">
                    <div class="relative">
                        <!-- Truck window frame -->
                        <div class="w-64 h-80 bg-gradient-to-b from-gray-700 to-gray-800 rounded-t-3xl border-4 border-gray-600 relative overflow-hidden">
                            <!-- Window reflection -->
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent"></div>

                            <!-- QR sticker -->
                            <div class="absolute top-8 left-1/2 -translate-x-1/2 bg-white rounded-xl p-3 shadow-2xl">
                                <div class="w-28 h-28 bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                <div class="text-center mt-2">
                                    <div class="text-gray-800 text-xs font-bold">SCAN TO FOLLOW</div>
                                    <div class="text-orange-600 text-[10px] font-medium">tacotrucktony.eventschedule.com</div>
                                </div>
                            </div>

                            <!-- Truck interior hint -->
                            <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
                        </div>

                        <!-- Truck body hint -->
                        <div class="w-72 h-4 bg-gradient-to-b from-orange-600 to-orange-700 -mt-1 mx-auto rounded-b-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter & Features Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Newsletter - HERO FEATURE -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-orange-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-4">Announce your week's spots in one click</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">No algorithm. 100% of your followers see it. Send your Monday-Friday locations every Sunday night.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-orange-500/20">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center">
                                <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">This Week's Spots</div>
                                <div class="text-orange-300 text-sm">Sending to 1,247 hungry fans...</div>
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex gap-2 text-gray-500 dark:text-gray-400"><span class="text-orange-400">Mon:</span> Downtown Food Park</div>
                            <div class="flex gap-2 text-gray-500 dark:text-gray-400"><span class="text-orange-400">Tue:</span> Tech Campus</div>
                            <div class="flex gap-2 text-gray-500 dark:text-gray-400"><span class="text-orange-400">Wed:</span> Farmers Market</div>
                            <div class="flex gap-2 text-gray-500 dark:text-gray-400"><span class="text-orange-400">Fri:</span> Brewery District</div>
                        </div>
                    </div>
                </div>

                <!-- Instant Notifications -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notifications
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Customers get pinged instantly</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Post a new location, your followers get notified. Last-minute spot change? They know immediately.</p>

                    <!-- Phone notification mockup -->
                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl p-4 max-w-xs mx-auto border border-gray-200 dark:border-white/10">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">TT</span>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Taco Truck Tony</div>
                                <div class="text-emerald-400 text-xs">New location posted!</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs mt-1">Tech Campus - Building A</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Promo Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">"Find us today" posts, ready to share</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Auto-generate Instagram-ready graphics with your location. One click to download and post.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-sky-500/30 to-cyan-500/30 rounded-xl border border-sky-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-orange-600/40 to-amber-600/40 rounded-lg flex flex-col items-center justify-center">
                                <svg aria-hidden="true" class="w-8 h-8 text-white mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <div class="text-white text-xs font-semibold">FIND US TODAY</div>
                                <div class="text-sky-300 text-[10px] mt-0.5">Downtown Food Park</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-sky-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg aria-hidden="true" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catering & Private Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Bookings
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Take catering inquiries</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Corporate lunches, weddings, private parties. Customers can request bookings right from your page. Zero platform fees.</p>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-200 dark:bg-white/10">
                            <div>
                                <span class="text-gray-900 dark:text-white text-sm">Corporate Lunch</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs block">50 people, March 15</span>
                            </div>
                            <span class="text-amber-400 text-sm font-medium">$650</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-amber-500/20 border border-amber-400/30">
                            <div>
                                <span class="text-gray-900 dark:text-white text-sm">Wedding Catering</span>
                                <span class="text-amber-300/70 text-xs block">150 people, June 22</span>
                            </div>
                            <span class="text-amber-300 text-sm font-medium">$3,200</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every kitchen on wheels
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From taco trucks to coffee carts, Event Schedule helps mobile vendors connect with customers.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Taco Trucks -->
                <x-sub-audience-card
                    name="Taco Trucks"
                    description="Authentic tacos, burritos, and Mexican street food - let fans track your daily location and specials."
                    icon-color="orange"
                    blog-slug="for-taco-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coffee & Beverage Carts -->
                <x-sub-audience-card
                    name="Coffee & Beverage Carts"
                    description="Mobile espresso, smoothies, juice bars - let caffeine seekers find their morning fix."
                    icon-color="amber"
                    blog-slug="for-coffee-beverage-carts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- BBQ & Smoker Trucks -->
                <x-sub-audience-card
                    name="BBQ & Smoker Trucks"
                    description="Low and slow on the go. Share when the brisket's ready and where fans can find it."
                    icon-color="red"
                    blog-slug="for-bbq-smoker-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Catering Businesses -->
                <x-sub-audience-card
                    name="Catering Businesses"
                    description="Private events and corporate lunches. Take bookings and manage your catering calendar in one place."
                    icon-color="emerald"
                    blog-slug="for-mobile-catering-businesses"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Pop-up Kitchens -->
                <x-sub-audience-card
                    name="Pop-up Kitchens"
                    description="Temporary restaurant experiences. Announce your next pop-up and build anticipation."
                    icon-color="violet"
                    blog-slug="for-popup-kitchens"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Festival Vendors -->
                <x-sub-audience-card
                    name="Festival Vendors"
                    description="Music festivals, county fairs, and outdoor events - let fans know which festivals you'll be serving at."
                    icon-color="teal"
                    blog-slug="for-festival-vendors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
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
                    Get your schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-orange-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add Your Spots</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Enter your weekly rotation or daily locations. Add addresses so customers can find you.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-orange-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share Your Link</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        One URL for all your locations. Put it in your bio, on your truck, everywhere.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-orange-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Feed Your Fans</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Customers follow and get notified of new locations. They find you, you feed them.
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
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-orange-600 to-amber-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop answering "Where are you today?"
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Let your regulars find you. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-orange-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Food Trucks & Vendors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Food Truck Location Management Software",
        "operatingSystem": "Web",
        "description": "Tell customers where to find your food truck today. Share your weekly rotation, email your regulars directly, and take catering bookings with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Daily and weekly location schedule with route visualization",
            "Direct newsletter to regulars - no algorithm middleman",
            "QR code for truck window - customers scan to follow",
            "Instant notifications when you post new locations",
            "Share-ready promotional graphics for social media",
            "Zero-fee catering and private event bookings"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        .text-gradient-food {
            background: linear-gradient(135deg, #f97316, #eab308);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
