<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Music Venues | Fill Your Stage</x-slot>
    <x-slot name="description">Your venue's concert calendar without Ticketmaster fees. Let bands apply to play, sell tickets directly to fans, and email your audience. Free forever.</x-slot>
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
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Is Event Schedule free for music venues?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your concert calendar, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage multiple stages and event types?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by stage, genre, or event type - main stage shows, acoustic sets, open mics, and DJ nights. Each event can have its own lineup, description, images, and ticket options."
                }
            },
            {
                "@type": "Question",
                "name": "How do music fans discover our shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your venue's schedule and receive email notifications for new shows. Embed your calendar on your website, share on social media, or send newsletters to subscribers with upcoming lineups."
                }
            },
            {
                "@type": "Question",
                "name": "Can I link performers to their own schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When you add a performer to your event, it can automatically appear on their schedule too. Both calendars stay in sync. This cross-linking helps fans discover your venue through the artists they follow."
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
        "keywords": "music venue calendar, concert venue schedule, venue booking requests, music venue management, free venue scheduling",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero: the stage                                           -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(78, 129, 250, 0.5), rgba(78, 129, 250, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(14, 165, 233, 0.45), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="h-2 w-2 animate-pulse rounded-full bg-blue-600 dark:bg-blue-400"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Concert Halls & Live Music Venues</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.4rem] font-black leading-[1.06] tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                <span class="es-mask"><span class="es-mask-line">The venue calendar that</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient es-gradient-anim">pays you, not Ticketmaster</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Bands apply to play. You sell tickets direct. Fans get email updates without you paying for reach. Zero platform fees.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Create Your Venue Calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Venue-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Concert Halls', 'Jazz Clubs', 'Rock Venues', 'Folk Rooms', 'Amphitheaters', 'Listening Rooms', 'Indie Clubs', 'Acoustic'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-blue-400 to-sky-400"></span>
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Problem / solution                                        -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 md:grid-cols-2">
                <div data-reveal>
                    <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white"><span class="text-red-500 dark:text-red-400">Tired of this?</span></h2>
                    <div class="space-y-4">
                        @foreach ([['Ticketing platforms eating into your door revenue', 'Service fees, facility fees, processing fees...'], ['Endless email chains with booking agents', 'Back and forth for every show, every detail'], ['Paying to reach your own fans on social', 'Algorithm changes, boosted posts, declining reach']] as [$t, $sub])
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-500/20"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                <div><div class="font-medium text-gray-900 dark:text-white">{{ $t }}</div><div class="text-sm text-gray-500 dark:text-gray-500">{{ $sub }}</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div data-reveal style="--reveal-delay: 0.1s;">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white"><span class="text-emerald-500 dark:text-emerald-400">Here's the fix</span></h2>
                    <div class="space-y-4">
                        @foreach ([['Sell tickets with $0 platform fees', "Just Stripe's 2.9% + 30c. That's it."], ['Artists apply with press kits built-in', 'Music samples, social links, everything in one place'], ['Email your fans directly for free', 'New show? One click reaches everyone who follows you']] as [$t, $sub])
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                <div><div class="font-medium text-gray-900 dark:text-white">{{ $t }}</div><div class="text-sm text-gray-500 dark:text-gray-500">{{ $sub }}</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Feature deep dives                                        -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-6xl space-y-24 px-4 sm:px-6 lg:px-8 lg:space-y-32">

            <!-- Artist booking -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                        Artist Booking
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Bands apply to play. You pick the good ones.</h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">No more digging through email, DMs, and voicemails. Artists submit through your calendar with their music, videos, social stats, and draw history. Listen to their tracks, check their following, book or pass.</p>
                    <ul class="space-y-3">
                        @foreach (['Embedded music players (Spotify, SoundCloud, YouTube)', 'Social follower counts at a glance', 'One-click approve or decline'] as $item)
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300"><svg aria-hidden="true" class="h-5 w-5 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" aria-hidden="true">
                        <div class="mb-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Booking Request</div>
                        <div class="mb-4 flex items-start gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-xl font-bold text-white">MS</div>
                            <div class="flex-1">
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Midnight Sons</div>
                                <div class="text-sm text-sky-600 dark:text-sky-300">Indie Rock / Brooklyn, NY</div>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400"><span>2.4K Spotify listeners</span><span>890 Instagram</span></div>
                            </div>
                        </div>
                        <div class="mb-4 rounded-xl bg-gray-100 p-3 dark:bg-[#0f0f14]">
                            <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Latest track</div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded bg-gradient-to-br from-emerald-500 to-teal-600"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
                                <div class="flex-1"><div class="text-sm text-gray-900 dark:text-white">Neon Heartbreak</div><div class="text-xs text-gray-500 dark:text-gray-500">3:42 / 45K streams</div></div>
                            </div>
                        </div>
                        <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">"We're touring the west coast in March and would love to stop in LA. We can draw 50-75 and have full backline..."</div>
                        <div class="flex gap-3">
                            <span class="flex-1 rounded-xl bg-emerald-500/20 py-2.5 text-center font-medium text-emerald-600 dark:text-emerald-400">Book them</span>
                            <span class="flex-1 rounded-xl bg-gray-100 py-2.5 text-center font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">Pass</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <!-- Multi-stage -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/[0.04]" aria-hidden="true">
                            <div class="border-b border-gray-200 bg-gray-100 px-4 py-3 dark:border-white/5 dark:bg-[#0d0d12]"><div class="text-sm font-medium text-gray-900 dark:text-white">Tonight at The Echo</div></div>
                            <div class="space-y-3 p-4">
                                <div class="rounded-xl border border-blue-500/20 bg-gradient-to-r from-blue-500/10 to-transparent p-4">
                                    <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-2"><div class="h-3 w-3 rounded-full bg-blue-500"></div><span class="font-medium text-gray-900 dark:text-white">Main Stage</span></div><span class="text-xs text-gray-500 dark:text-gray-400">Cap: 350</span></div>
                                    <div class="ml-5 space-y-2 text-sm">
                                        <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">8:00pm - The Openers</span><span class="text-gray-500 dark:text-gray-500">45 min</span></div>
                                        <div class="flex items-center justify-between"><span class="font-medium text-gray-900 dark:text-white">9:30pm - Midnight Sons</span><span class="text-blue-600 dark:text-blue-300">Headliner</span></div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-sky-500/20 bg-gradient-to-r from-sky-500/10 to-transparent p-4">
                                    <div class="mb-2 flex items-center justify-between"><div class="flex items-center gap-2"><div class="h-3 w-3 rounded-full bg-sky-500"></div><span class="font-medium text-gray-900 dark:text-white">Back Room</span></div><span class="text-xs text-gray-500 dark:text-gray-400">Cap: 75</span></div>
                                    <div class="ml-5 space-y-2 text-sm"><div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">8:30pm - Jazz Trio</span><span class="text-gray-500 dark:text-gray-500">2 sets</span></div></div>
                                </div>
                                <div class="rounded-xl border border-amber-500/20 bg-gradient-to-r from-amber-500/10 to-transparent p-4 opacity-60">
                                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><div class="h-3 w-3 rounded-full bg-amber-500/50"></div><span class="font-medium text-gray-900 dark:text-white">Outdoor Patio</span></div><span class="text-xs italic text-amber-500 dark:text-amber-400/70">Closed for season</span></div>
                                </div>
                            </div>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2" data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Multiple Rooms
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Main stage. Back room. Patio. All in one place.</h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Run separate calendars for each performance space. Visitors filter by room. Artists know exactly where they're playing. No double-booking, no confusion at load-in.</p>
                    <ul class="space-y-3">
                        @foreach (['Separate capacities per room', 'Fans filter by their preferred space', 'Seasonal rooms (open/close as needed)'] as $item)
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300"><svg aria-hidden="true" class="h-5 w-5 shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Ticket comparison -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Ticketing
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Keep your door money. All of it.</h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">On a $35 ticket, the big platforms take $10-15 in fees. We take $0. Stripe handles payment processing at their standard rate, and the rest goes straight to your bank account.</p>
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                        <div class="mb-2 font-medium text-emerald-600 dark:text-emerald-300">On 1,000 tickets at $35 each:</div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><div class="text-gray-500 dark:text-gray-400">Ticketmaster</div><div class="font-semibold text-gray-900 dark:text-white">You keep: ~$25,000</div></div>
                            <div><div class="text-emerald-600 dark:text-emerald-300">Event Schedule</div><div class="font-semibold text-gray-900 dark:text-white">You keep: ~$33,950</div></div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4" data-reveal="panel">
                    <div class="rounded-2xl border border-red-500/20 bg-red-500/5 p-5">
                        <div class="mb-4 flex items-center justify-between"><span class="text-sm font-medium text-red-500 line-through dark:text-red-300">Other platforms</span><span class="text-sm text-gray-500 dark:text-gray-400">$35 ticket</span></div>
                        <div class="mb-4 space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Service fee</span><span class="text-red-500 dark:text-red-300">+$8.50</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Facility charge</span><span class="text-red-500 dark:text-red-300">+$3.00</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Order processing</span><span class="text-red-500 dark:text-red-300">+$2.95</span></div>
                        </div>
                        <div class="flex justify-between border-t border-red-500/20 pt-3"><span class="text-gray-500 dark:text-gray-400">Fan pays</span><span class="text-lg font-bold text-red-500 dark:text-red-400">$49.45</span></div>
                    </div>
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-5">
                        <div class="mb-4 flex items-center justify-between"><span class="text-sm font-medium text-emerald-600 dark:text-emerald-300">Event Schedule</span><span class="text-sm text-gray-500 dark:text-gray-400">$35 ticket</span></div>
                        <div class="mb-4 space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-500">Platform fee</span><span class="text-emerald-600 dark:text-emerald-300">$0.00</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-500">Stripe processing</span><span class="text-gray-500 dark:text-gray-400">~$1.32</span></div>
                        </div>
                        <div class="flex justify-between border-t border-emerald-500/30 pt-3"><span class="text-gray-500 dark:text-gray-400">Fan pays</span><span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">$35.00</span></div>
                    </div>
                </div>
            </div>

            <!-- Fan newsletter -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative mx-auto max-w-md" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/[0.04]" aria-hidden="true">
                            <div class="border-b border-gray-200 bg-gradient-to-r from-sky-500/20 to-blue-500/20 px-5 py-4 dark:border-white/5">
                                <div class="flex items-center gap-2"><svg aria-hidden="true" class="h-5 w-5 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg><span class="font-medium text-gray-900 dark:text-white">New Show Announcement</span></div>
                            </div>
                            <div class="p-5">
                                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">To: 2,847 venue followers</div>
                                <div class="mb-4 rounded-xl bg-gradient-to-br from-blue-500/20 to-sky-500/20 p-4">
                                    <div class="mb-1 text-xs font-medium text-sky-600 dark:text-sky-300">JUST ANNOUNCED</div>
                                    <div class="mb-1 text-xl font-bold text-gray-900 dark:text-white">Phoebe Bridgers</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">Saturday, March 15 / Doors 7pm</div>
                                    <div class="mt-3 flex items-center gap-2"><span class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 text-xs text-gray-900 dark:bg-white/10 dark:text-white">$45</span><span class="inline-flex items-center rounded-full bg-blue-500/30 px-3 py-1 text-xs text-blue-600 dark:text-blue-300">On Sale Now</span></div>
                                </div>
                                <div class="flex items-center justify-between text-xs"><div class="flex items-center gap-4"><span class="text-emerald-600 dark:text-emerald-400">2,704 delivered</span><span class="text-sky-600 dark:text-sky-300">892 opened</span></div><span class="text-gray-500 dark:text-gray-400">33% open rate</span></div>
                            </div>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2" data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        Fan Newsletter
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Your fans. Your inbox. No algorithm in between.</h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">When someone follows your venue, they're opting in to hear from you. Announce a show, and it goes straight to their inbox - not buried in a feed, not paywalled behind boosted posts.</p>
                    <ul class="space-y-3">
                        @foreach (['One-click announcements to all followers', 'See open rates and engagement', 'Never pay to reach your own audience'] as $item)
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300"><svg aria-hidden="true" class="h-5 w-5 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Also included                                             -->
    <!-- ============================================================ -->
    @php
        $alsoIncluded = [
            ['QR Door Scanning', 'Use any smartphone. No hardware needed.', 'bg-blue-500/20', 'text-blue-500 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />'],
            ['Recurring Shows', 'Jazz Wednesdays auto-repeat every week.', 'bg-sky-500/20', 'text-sky-500 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
            ['Promo Graphics', 'Auto-generate flyers for socials.', 'bg-amber-500/20', 'text-amber-500 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
            ['Venue Analytics', 'See which genres fill your room.', 'bg-cyan-500/20', 'text-cyan-500 dark:text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />'],
            ['Google Cal Sync', 'Two-way sync with your existing calendar.', 'bg-rose-500/20', 'text-rose-500 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />'],
            ['Load-in Info', 'Share parking, backline, PA details.', 'bg-emerald-500/20', 'text-emerald-500 dark:text-emerald-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />'],
            ['Live Streaming', 'Sell tickets to virtual attendees.', 'bg-sky-500/20', 'text-sky-500 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
            ['Embed Anywhere', 'Add your calendar to your website.', 'bg-blue-500/20', 'text-blue-500 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Also included</h2>
                <p class="text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Everything else you need to run your room</p>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="60">
                @foreach ($alsoIncluded as [$name, $desc, $chip, $text, $icon])
                    <div data-reveal class="rounded-xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-1 hover:border-blue-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/30">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg {{ $chip }}">
                            <svg aria-hidden="true" class="h-5 w-5 {{ $text }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                        </div>
                        <h3 class="mb-1 font-medium text-gray-900 dark:text-white">{{ $name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-500">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for every kind of <span class="text-gradient">music room</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    50 seats or 5,000 - if you book live music, this is for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
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

    <!-- ============================================================ -->
    <!-- 6. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="opacity: 0.28;"></div>
                <div class="es-aurora es-aurora-2" style="opacity: 0.22;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Get your venue calendar online in <span class="text-gradient">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Create Your Calendar', 'Sign up and add your venue details. Set up stages or rooms as sub-schedules if you have multiple spaces.'], ['2', 'Add Your Events', 'Add events manually, import from Google Calendar, or accept booking requests from performers.'], ['3', 'Share & Sell', 'Embed on your website, share on social, and start selling tickets with QR check-in.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-musicians', 'Musicians']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything music venues ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for music venues?', 'Yes. Event Schedule is free forever for sharing your concert calendar, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I manage multiple stages and event types?', 'Yes. Use sub-schedules to organize by stage, genre, or event type - main stage shows, acoustic sets, open mics, and DJ nights. Each event can have its own lineup, description, images, and ticket options.'],
                    ['How do music fans discover our shows?', 'Fans can follow your venue\'s schedule and receive email notifications for new shows. Embed your calendar on your website, share on social media, or send newsletters to subscribers with upcoming lineups.'],
                    ['Can I link performers to their own schedules?', 'Yes. When you add a performer to your event, it can automatically appear on their schedule too. Both calendars stay in sync. This cross-linking helps fans discover your venue through the artists they follow.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="opacity: 0.3;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop paying to fill your <span class="text-gradient">own room</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Your venue. Your calendar. Your ticket revenue. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Create Your Venue Calendar
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
