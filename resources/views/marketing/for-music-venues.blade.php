<x-marketing-layout>
    <x-slot name="title">Event Schedule for Music Venues | Concert Calendar & Ticketing Software</x-slot>
    <x-slot name="description">Your venue's concert calendar without Ticketmaster fees. Let bands apply to play, sell tickets directly to fans. Free forever.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Music Venues</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Music Venues",
        "description": "Your venue's concert calendar without Ticketmaster fees. Let bands apply to play, sell tickets directly to fans. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Music Venues"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 lg:py-32 overflow-hidden">
        <!-- Subtle stage lighting effect -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-blue-600/20 via-blue-600/5 to-transparent blur-[80px]"></div>
        </div>

        <div class="absolute inset-0 grid-pattern bg-[size:60px_60px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/10 border border-gray-200 dark:border-white/10 mb-6">
                    <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-pulse"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300">For Concert Halls & Live Music Venues</span>
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                    The venue calendar that<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-400 to-blue-400">pays you, not Ticketmaster</span>
                </h1>

                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-10">
                    Bands apply to play. You sell tickets direct. Fans get email updates without you paying for reach. Zero platform fees.
                </p>

                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Create Your Venue Calendar
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- The Problem / Solution -->
    <section class="bg-gray-50 dark:bg-[#0d0d12] py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- The Problem -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        <span class="text-red-400">Tired of this?</span>
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Ticketing platforms taking 15-25% of your door</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">Service fees, facility fees, processing fees...</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Endless email chains with booking agents</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">Back and forth for every show, every detail</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Paying to reach your own fans on social</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">Algorithm changes, boosted posts, declining reach</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- The Solution -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        <span class="text-emerald-400">Here's the fix</span>
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Sell tickets with $0 platform fees</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">Just Stripe's 2.9% + 30c. That's it.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Artists apply with press kits built-in</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">Music samples, social links, everything in one place</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Email your fans directly for free</div>
                                <div class="text-gray-600 dark:text-gray-500 text-sm">New show? One click reaches everyone who follows you</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Deep Dives -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Feature 1: Artist Booking -->
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-32">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        Artist Booking
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Bands apply to play.<br>You pick the good ones.
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">
                        No more digging through email, DMs, and voicemails. Artists submit through your calendar with their music, videos, social stats, and draw history. Listen to their tracks, check their following, book or pass.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Embedded music players (Spotify, SoundCloud, YouTube)
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Social follower counts at a glance
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            One-click approve or decline
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="bg-gray-50 dark:bg-[#12121a] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Booking Request</div>
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-xl font-bold">MS</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white font-semibold text-lg">Midnight Sons</div>
                                <div class="text-sky-300 text-sm">Indie Rock / Brooklyn, NY</div>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>2.4K Spotify listeners</span>
                                    <span>890 Instagram</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-3 mb-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Latest track</div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-900 dark:text-white text-sm">Neon Heartbreak</div>
                                    <div class="text-gray-600 dark:text-gray-500 text-xs">3:42 / 45K streams</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            "We're touring the west coast in March and would love to stop in LA. We can draw 50-75 and have full backline..."
                        </div>

                        <div class="flex gap-3">
                            <button class="flex-1 py-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 font-medium hover:bg-emerald-500/30 transition">
                                Book them
                            </button>
                            <button class="flex-1 py-2.5 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-200 dark:hover:bg-white/10 transition">
                                Pass
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature 2: Multi-Stage -->
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-32">
                <div class="order-2 lg:order-1">
                    <div class="bg-gray-50 dark:bg-[#12121a] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gray-100 dark:bg-[#0d0d12] px-4 py-3 border-b border-gray-200 dark:border-white/5">
                            <div class="text-gray-900 dark:text-white font-medium text-sm">Tonight at The Echo</div>
                        </div>
                        <div class="p-4 space-y-3">
                            <!-- Main Stage -->
                            <div class="bg-gradient-to-r from-blue-500/10 to-transparent rounded-xl border border-blue-500/20 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                        <span class="text-gray-900 dark:text-white font-medium">Main Stage</span>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">Cap: 350</span>
                                </div>
                                <div class="ml-5 space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">8:00pm - The Openers</span>
                                        <span class="text-gray-600 dark:text-gray-500">45 min</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-900 dark:text-white font-medium">9:30pm - Midnight Sons</span>
                                        <span class="text-blue-300">Headliner</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Back Room -->
                            <div class="bg-gradient-to-r from-sky-500/10 to-transparent rounded-xl border border-sky-500/20 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-sky-500"></div>
                                        <span class="text-gray-900 dark:text-white font-medium">Back Room</span>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">Cap: 75</span>
                                </div>
                                <div class="ml-5 space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">8:30pm - Jazz Trio</span>
                                        <span class="text-gray-600 dark:text-gray-500">2 sets</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Patio -->
                            <div class="bg-gradient-to-r from-amber-500/10 to-transparent rounded-xl border border-amber-500/20 p-4 opacity-60">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-amber-500/50"></div>
                                        <span class="text-gray-900 dark:text-white font-medium">Outdoor Patio</span>
                                    </div>
                                    <span class="text-amber-400/70 text-xs italic">Closed for season</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Multiple Rooms
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Main stage. Back room.<br>Patio. All in one place.
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">
                        Run separate calendars for each performance space. Visitors filter by room. Artists know exactly where they're playing. No double-booking, no confusion at load-in.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Separate capacities per room
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Fans filter by their preferred space
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Seasonal rooms (open/close as needed)
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Feature 3: Ticket Comparison -->
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-32">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ticketing
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Keep your door money.<br>All of it.
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">
                        On a $35 ticket, the big platforms take $10-15 in fees. We take $0. Stripe handles payment processing at their standard rate, and the rest goes straight to your bank account.
                    </p>
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4">
                        <div class="text-emerald-300 font-medium mb-2">On 1,000 tickets at $35 each:</div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Ticketmaster</div>
                                <div class="text-gray-900 dark:text-white font-semibold">You keep: ~$25,000</div>
                            </div>
                            <div>
                                <div class="text-emerald-300">Event Schedule</div>
                                <div class="text-gray-900 dark:text-white font-semibold">You keep: ~$33,950</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="space-y-4">
                        <!-- Their fees -->
                        <div class="bg-red-500/5 rounded-2xl border border-red-500/20 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-red-300 text-sm font-medium line-through">Other platforms</span>
                                <span class="text-gray-500 dark:text-gray-400 text-sm">$35 ticket</span>
                            </div>
                            <div class="space-y-2 text-sm mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Service fee</span>
                                    <span class="text-red-300">+$8.50</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Facility charge</span>
                                    <span class="text-red-300">+$3.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Order processing</span>
                                    <span class="text-red-300">+$2.95</span>
                                </div>
                            </div>
                            <div class="border-t border-red-500/20 pt-3 flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Fan pays</span>
                                <span class="text-red-400 font-bold text-lg">$49.45</span>
                            </div>
                        </div>

                        <!-- Our fees -->
                        <div class="bg-emerald-500/5 rounded-2xl border border-emerald-500/30 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-emerald-300 text-sm font-medium">Event Schedule</span>
                                <span class="text-gray-500 dark:text-gray-400 text-sm">$35 ticket</span>
                            </div>
                            <div class="space-y-2 text-sm mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Platform fee</span>
                                    <span class="text-emerald-300">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Stripe processing</span>
                                    <span class="text-gray-500 dark:text-gray-400">~$1.32</span>
                                </div>
                            </div>
                            <div class="border-t border-emerald-500/30 pt-3 flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Fan pays</span>
                                <span class="text-emerald-400 font-bold text-lg">$35.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature 4: Fan Newsletter -->
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <div class="bg-gray-50 dark:bg-[#12121a] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden max-w-md mx-auto">
                        <div class="bg-gradient-to-r from-sky-500/20 to-blue-500/20 px-5 py-4 border-b border-gray-200 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <svg aria-hidden="true" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-900 dark:text-white font-medium">New Show Announcement</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="text-gray-500 dark:text-gray-400 text-xs mb-3">To: 2,847 venue followers</div>
                            <div class="bg-gradient-to-br from-blue-500/20 to-sky-500/20 rounded-xl p-4 mb-4">
                                <div class="text-sky-300 text-xs font-medium mb-1">JUST ANNOUNCED</div>
                                <div class="text-gray-900 dark:text-white text-xl font-bold mb-1">Phoebe Bridgers</div>
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Saturday, March 15 / Doors 7pm</div>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-xs">$45</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/30 text-blue-300 text-xs">On Sale Now</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-4">
                                    <span class="text-emerald-400">2,704 delivered</span>
                                    <span class="text-sky-300">892 opened</span>
                                </div>
                                <span class="text-gray-500 dark:text-gray-400">33% open rate</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        Fan Newsletter
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Your fans. Your inbox.<br>No algorithm in between.
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">
                        When someone follows your venue, they're opting in to hear from you. Announce a show, and it goes straight to their inbox - not buried in a feed, not paywalled behind boosted posts.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            One-click announcements to all followers
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            See open rates and engagement
                        </li>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Never pay to reach your own audience
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Also Included -->
    <section class="bg-gray-50 dark:bg-[#0d0d12] py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">Also included</h2>
                <p class="text-gray-500 dark:text-gray-400">Everything else you need to run your room</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-blue-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">QR Door Scanning</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Use any smartphone. No hardware needed.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-sky-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-sky-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Recurring Shows</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Jazz Wednesdays auto-repeat every week.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-amber-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Promo Graphics</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Auto-generate flyers for socials.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-cyan-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Venue Analytics</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">See which genres fill your room.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-rose-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-rose-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Google Cal Sync</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Two-way sync with your existing calendar.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-emerald-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Load-in Info</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Share parking, backline, PA details.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-sky-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-sky-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Live Streaming</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Sell tickets to virtual attendees.</p>
                </div>

                <div class="bg-white dark:bg-[#12121a] rounded-xl border border-gray-200 dark:border-white/5 p-5 hover:border-blue-500/30 transition">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Embed Anywhere</h3>
                    <p class="text-gray-600 dark:text-gray-500 text-sm">Add your calendar to your website.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every kind of music room
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    50 seats or 5,000 - if you book live music, this is for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Concert Halls -->
                <x-sub-audience-card
                    name="Concert Halls"
                    description="Seated performances, classical music, acoustic shows. Manage reserved seating and season subscriptions."
                    icon-color="violet"
                    blog-slug="for-concert-halls"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Live Music Bars & Clubs -->
                <x-sub-audience-card
                    name="Live Music Bars & Clubs"
                    description="Standing-room venues with regular programming. Build a local following for weekly shows."
                    icon-color="indigo"
                    blog-slug="for-small-music-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Jazz Clubs -->
                <x-sub-audience-card
                    name="Jazz Clubs"
                    description="Intimate sets, residencies, and guest headliners. Cultivate your jazz community."
                    icon-color="purple"
                    blog-slug="for-mid-size-music-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Folk & Acoustic Venues -->
                <x-sub-audience-card
                    name="Folk & Acoustic Venues"
                    description="Singer-songwriter nights, open mics, and listening rooms. Create a space for acoustic performances."
                    icon-color="amber"
                    blog-slug="for-house-concerts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Rock & Indie Venues -->
                <x-sub-audience-card
                    name="Rock & Indie Venues"
                    description="Touring bands, local acts, and multi-band bills. Manage green room schedules and load-in times."
                    icon-color="rose"
                    blog-slug="for-multi-purpose-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Outdoor Amphitheaters -->
                <x-sub-audience-card
                    name="Outdoor Amphitheaters"
                    description="Seasonal programming, festival-style events. Handle weather-dependent scheduling."
                    icon-color="emerald"
                    blog-slug="for-outdoor-amphitheaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
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
                <a href="{{ marketing_url('/for-venues') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Venues</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-nightclubs') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Nightclubs</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-bars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Bars</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-musicians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Musicians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-sky-600 to-blue-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-sky-500/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop paying to fill your own room
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Your venue. Your calendar. Your ticket revenue. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Create Your Venue Calendar
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-white/60 text-sm">No credit card required</p>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Music Venues",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Music Venue Management Software",
        "operatingSystem": "Web",
        "description": "Your venue's concert calendar without the Ticketmaster fees. Let bands apply to play, sell tickets directly to fans, and manage load-in to encore.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Zero platform fee ticketing",
            "Artist booking applications",
            "Multi-stage venue support",
            "Show day timeline management",
            "QR code door check-in",
            "Venue analytics",
            "Direct fan newsletters"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
