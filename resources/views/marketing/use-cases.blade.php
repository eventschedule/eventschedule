<x-marketing-layout>
    <x-slot name="title">Event Scheduling for Every Industry | Event Schedule</x-slot>
    <x-slot name="description">Event scheduling software for musicians, venues, restaurants, and theaters. Share events, sell tickets, send newsletters. Free forever with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">Use Cases</x-slot>

    @php
        // Shared with /for-talent, which renders the same twelve cards. Editing a
        // blurb or tag in one place used to leave the other page stale.
        $performers = config('marketing_audiences.performers');
        $venues = [
            ['url' => '/for-bars', 'name' => 'Bars & Pubs', 'blurb' => 'Keep your entertainment calendar fresh and bring in crowds.', 'tags' => ['Craft Beer Bars', 'Wine Bars', 'Sports Bars', 'Cocktail Lounges', 'Irish & British Pubs', 'Dive Bars'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />'],
            ['url' => '/for-nightclubs', 'name' => 'Nightclubs', 'blurb' => 'Promote DJ lineups, themed nights, and special events.', 'tags' => ['Dance Clubs & EDM', 'Hip-Hop & Urban', 'Latin Clubs', 'Rooftop Clubs', 'Underground & Warehouse', 'VIP Lounges'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />'],
            ['url' => '/for-music-venues', 'name' => 'Music Venues', 'blurb' => 'Manage concert schedules and sell tickets for every show.', 'tags' => ['Concert Halls', 'Live Music Bars', 'Jazz Clubs', 'Folk & Acoustic', 'Rock & Indie Venues', 'Outdoor Amphitheaters'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['url' => '/for-theaters', 'name' => 'Theaters', 'blurb' => 'Share your season schedule and sell tickets for every production.', 'tags' => ['Community Theaters', 'Regional Theaters', 'Black Box Theaters', 'Dinner Theaters', 'Children\'s Theaters', 'Outdoor Amphitheaters'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />'],
            ['url' => '/for-comedy-clubs', 'name' => 'Comedy Clubs', 'blurb' => 'Fill seats with a lineup calendar your audience will love.', 'tags' => ['Stand-up Clubs', 'Improv Theaters', 'Open Mic Venues', 'Comedy Bars', 'Sketch Comedy Venues', 'Live Podcast Studios'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['url' => '/for-restaurants', 'name' => 'Restaurants', 'blurb' => 'Promote special dinners, live music nights, and tasting events.', 'tags' => ['Fine Dining', 'Wine Bars & Tapas', 'Farm-to-Table', 'Supper Clubs', 'Casual Dining & Bistros', 'Chef\'s Tables'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z" />'],
            ['url' => '/for-breweries-and-wineries', 'name' => 'Breweries & Wineries', 'blurb' => 'Share tastings, tap takeovers, live music, and seasonal events.', 'tags' => ['Craft Breweries', 'Brewpubs & Taprooms', 'Wineries & Vineyards', 'Cideries & Orchards', 'Meaderies & Distilleries', 'Taproom-Only'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />'],
            ['url' => '/for-art-galleries', 'name' => 'Art Galleries', 'blurb' => 'Promote exhibitions, openings, and artist talks to collectors and fans.', 'tags' => ['Contemporary Art', 'Fine Art Studios', 'Photography Galleries', 'Craft & Maker Studios', 'Artist Collectives', 'Pop-up Spaces'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />'],
            ['url' => '/for-community-centers', 'name' => 'Community Centers', 'blurb' => 'Keep your community informed about classes, meetings, and events.', 'tags' => ['Recreation Centers', 'Senior Centers', 'Youth Centers', 'Cultural Centers', 'Neighborhood Centers', 'Faith-Based Centers'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
            ['url' => '/for-farmers-markets', 'name' => 'Farmers Markets', 'blurb' => 'Share your market schedule and build a loyal shopper community.', 'tags' => ['Weekly Farmers Markets', 'Artisan & Craft Markets', 'Flea Markets', 'Holiday Markets', 'Night Markets', 'Specialty Food Markets'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />'],
            ['url' => '/for-hotels-and-resorts', 'name' => 'Hotels & Resorts', 'blurb' => 'Elevate the guest experience with activity calendars and events.', 'tags' => ['Boutique Hotels', 'Beach Resorts', 'Conference Hotels', 'Spa & Wellness', 'Mountain Lodges', 'Casino Hotels'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />'],
            ['url' => '/for-libraries', 'name' => 'Libraries', 'blurb' => 'Share programs, author events, and community activities with patrons.', 'tags' => ['Public Libraries', 'University Libraries', 'Community Reading Rooms', 'Children\'s Libraries', 'Archive Centers', 'Mobile Libraries'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
        ];
        $online = [
            ['url' => '/for-webinars', 'name' => 'Webinars', 'blurb' => 'Host webinars with free registration, paid tickets, and one link field for any platform.', 'tags' => ['Product Demos', 'Training Sessions', 'Workshops', 'Panel Discussions', 'All-Hands', 'Lectures'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
            ['url' => '/for-online-classes', 'name' => 'Online Classes', 'blurb' => 'Schedule recurring classes, sell tickets, and put the join link on every student\'s ticket.', 'tags' => ['Yoga & Fitness', 'Cooking Classes', 'Art & Music Lessons', 'Language Courses', 'Coding Bootcamps', 'Tutoring'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />'],
            ['url' => '/for-virtual-conferences', 'name' => 'Virtual Conferences', 'blurb' => 'Run multi-day programs with an agenda on each event and as many ticket types as you need.', 'tags' => ['Tech Summits', 'Industry Conferences', 'Company Retreats', 'Professional Summits', 'Annual Meetings', 'Panel Events'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />'],
            ['url' => '/for-live-qa-sessions', 'name' => 'Live Q&A Sessions', 'blurb' => 'Schedule live Q&A sessions with registration, ticketing, and streaming links.', 'tags' => ['AMAs', 'Town Halls', 'Expert Panels', 'Fireside Chats', 'Community Q&As', 'Office Hours'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />'],
            ['url' => '/for-watch-parties', 'name' => 'Watch Parties', 'blurb' => 'Schedule screenings with registration and tickets, and one link field for any platform.', 'tags' => ['Premiere Screenings', 'Movie Nights', 'Sports Watch Parties', 'Series Finales', 'Documentary Screenings', 'Gaming Events'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />'],
            ['url' => '/for-live-concerts', 'name' => 'Live Concerts', 'blurb' => 'List a hybrid show once, with the room and the join link on one event, and email fans directly.', 'tags' => ['Acoustic Sets', 'Rock Shows', 'Jazz Nights', 'Festival Streams', 'Album Release Shows', 'DJ Sets'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />'],
        ];
        $faqs = [
            ['q' => 'Is Event Schedule free?', 'a' => 'Yes. Event Schedule is free forever for creating and sharing your event calendar, and the free plan also sells up to 25 paid tickets a month and scans them at the door. Pro lifts that to unlimited ticket sales and adds event graphics, the API and the live check-in dashboard; Enterprise adds custom domains and extra team members. There are no platform fees on ticket sales - you only pay Stripe\'s standard processing fees.'],
            ['q' => 'What types of events can I manage?', 'a' => 'Any kind. Musicians share gig schedules, bars list their weekly lineups, theaters manage their season calendars, fitness instructors schedule classes, and conference organizers run multi-day programs. Event Schedule works for in-person events, online events, and hybrid events across every industry.'],
            ['q' => 'Can I sell tickets with Event Schedule?', 'a' => 'Yes. Sell tickets directly through your event page with Stripe integration. Buyers get QR code tickets for easy check-in at the door. There are no platform fees - you only pay Stripe\'s standard processing fees, so you keep more of your revenue.'],
            ['q' => 'Does Event Schedule work for online events?', 'a' => 'Yes. Paste the link people join on into any event and the whole link is printed on their ticket, while the public listing shows only the domain. It is a link and not an integration, so Zoom, Google Meet, YouTube Live, Twitch or a page on your own site all work the same way. You can sell tickets for virtual events, run webinars, schedule online classes, and manage virtual conferences.'],
            ['q' => 'Is Event Schedule open source?', 'a' => 'Yes. Event Schedule is fully open source. You can use the hosted version at eventschedule.com or selfhost it on your own server for complete control over your data and branding. The selfhosted version includes all features with no limits.'],
        ];

        // The three schedule-type hubs and the developer page are not cards in the
        // grids, so they are listed explicitly for the ItemList and the counts.
        $hubPages = [
            ['url' => '/for-talent', 'name' => 'For Talent'],
            ['url' => '/for-venues', 'name' => 'For Venues'],
            ['url' => '/for-curators', 'name' => 'For Curators'],
            ['url' => '/for-ai-agents', 'name' => 'For AI Agents'],
        ];

        // Everything the page links, in document order - drives numberOfItems so the
        // schema can never drift from the markup.
        $allAudiences = array_merge(
            [$hubPages[0]], $performers,
            [$hubPages[1]], $venues,
            [$hubPages[2]],
            $online,
            [$hubPages[3]],
        );

    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Event Scheduling for Every Industry",
        "description": "Event scheduling and ticketing for musicians, venues, restaurants, theaters, online events, and more.",
        "url": "{{ url('/use-cases') }}",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": {{ count($allAudiences) }},
            "itemListElement": [
                @foreach ($allAudiences as $i => $a)
                {
                    "@type": "ListItem",
                    "position": {{ $i + 1 }},
                    "name": {!! \App\Utils\SeoUtils::jsonLd($a['name']) !!},
                    "url": {!! \App\Utils\SeoUtils::jsonLd(marketing_url($a['url'])) !!}
                }@if (! $loop->last),@endif
                @endforeach
            ]
        }
    }
    </script>
    </x-slot>

    <x-seo.faq-schema :items="$faqs" />

    <style {!! nonce_attr() !!}>
        .text-gradient-usecases {
            /* Light-mode stops stay at or above 3:1 on white, which large display text needs. */
            background: linear-gradient(135deg, #2563eb 0%, #0284c7 50%, #0891b2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-usecases {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-usecases {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero (compact - on a directory page the cards are the CTA)  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(46svh-4rem)] items-center overflow-hidden bg-white py-14 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.28), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                </span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Whatever you run</span>
            </div>

            <h1 class="es-balance mb-5 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Whatever you put on,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-usecases">somebody here runs it</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-7 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Share your events, <a href="{{ marketing_url('/features/ticketing') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">sell tickets</a> with zero platform fees, and <a href="{{ marketing_url('/features/newsletters') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">email your followers</a> when you have new dates. Free forever, open source, and <a href="{{ marketing_url('/selfhost') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">selfhostable</a> if you want it on your own server.
            </p>

            {{-- Jump row: the mobile wayfinding answer, since the dot nav is lg-only. --}}
            <nav class="es-fade-up es-d-3 flex flex-wrap items-center justify-center gap-2 lg:hidden" aria-label="Jump to a category">
                <a href="#performers" class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">Performers</a>
                <a href="#venues" class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3.5 py-1.5 text-sm font-medium text-amber-800 transition-colors hover:bg-amber-100 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">Venues</a>
                <a href="#curators" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">Curators</a>
                <a href="#online" class="inline-flex items-center gap-1.5 rounded-full border border-cyan-200 bg-cyan-50 px-3.5 py-1.5 text-sm font-medium text-cyan-700 transition-colors hover:bg-cyan-100 dark:border-cyan-400/30 dark:bg-cyan-500/10 dark:text-cyan-300 dark:hover:bg-cyan-500/20">Online</a>
                <a href="#developers" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:border-slate-400/30 dark:bg-slate-500/10 dark:text-slate-300 dark:hover:bg-slate-500/20">Developers</a>
            </nav>
        </div>
    </section>


    <!-- ============================================================ -->
    <!-- Performers & Artists                                        -->
    <!-- ============================================================ -->
    <section id="performers" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/20">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">For Performers &amp; Artists</h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">Musicians, DJs, performers, and artists who want to share their upcoming shows and build their audience. Sync with Google Calendar, let venues add you to their lineup through booking requests, and email your fans directly whenever you announce new dates.</p>

                <a href="{{ marketing_url('/for-talent') }}"
                   class="group mt-5 inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold ring-1 transition-all text-blue-700 ring-blue-200 hover:bg-blue-50 dark:text-blue-300 dark:ring-blue-400/30 dark:hover:bg-blue-500/10">
                    See the full Talent guide
                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">

                @foreach ($performers as $a)
                    <x-marketing.audience-card
                        :url="marketing_url($a['url'])"
                        :name="$a['name']"
                        :blurb="$a['blurb']"
                        accent="blue"
                        :tags="$a['tags']">
                        <x-slot name="icon">{!! $a['icon'] !!}</x-slot>
                    </x-marketing.audience-card>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Venues & Event Spaces                                       -->
    <!-- ============================================================ -->
    <section id="venues" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                        <svg class="h-6 w-6 text-amber-700 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">For Venues &amp; Event Spaces</h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">Bars, clubs, theaters, restaurants, and every other space that hosts events. Publish your lineup, take booking requests from performers, sell tickets with zero platform fees, and keep your regulars in the loop.</p>

                <a href="{{ marketing_url('/for-venues') }}"
                   class="group mt-5 inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold ring-1 transition-all text-amber-800 ring-amber-200 hover:bg-amber-50 dark:text-amber-300 dark:ring-amber-400/30 dark:hover:bg-amber-500/10">
                    See the full Venues guide
                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">

                @foreach ($venues as $a)
                    <x-marketing.audience-card
                        :url="marketing_url($a['url'])"
                        :name="$a['name']"
                        :blurb="$a['blurb']"
                        accent="amber"
                        :tags="$a['tags']">
                        <x-slot name="icon">{!! $a['icon'] !!}</x-slot>
                    </x-marketing.audience-card>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Curators & Promoters                                        -->
    <!-- ============================================================ -->
    <section id="curators" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-500/20">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">For Curators &amp; Promoters</h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">Event promoters, bloggers, and community organizers who aggregate and share events from multiple sources. Import events with AI, pull lineups from venues and performers automatically, and become the go-to calendar for your local scene.</p>
            </div>

            <a href="{{ marketing_url('/for-curators') }}" data-reveal data-tilt="1.5"
               class="es-bento es-tilt-inner group relative block overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow duration-200 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                <div class="relative">
                    <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-400 lg:text-3xl">Curate events from across your scene</h3>
                    <p class="mb-6 max-w-3xl text-base text-gray-600 dark:text-gray-400 lg:text-lg">Aggregate events from multiple venues and performers into one shareable schedule. Be the go-to source for what's happening in your community.</p>
                    <p class="mb-6 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">{{ implode(' · ', ['Event Promoters', 'Music Bloggers', 'Community Organizers', 'Scene Guides', 'Local Media', 'Tourism Boards']) }}</p>

                    @php
                        $curatorTiles = [
                            // Was "Paste a URL or image": the AI import screen takes pasted text
                            // or a dropped image only. Nothing fetches a URL for you - the
                            // URL/city importer is the selfhost-only ImportCuratorEvents command.
                            ['AI Import', 'Paste the text or drop a flyer photo, AI fills in the details', 'M13 10V3L4 14h7v7l9-11h-7z'],
                            ['Aggregation', 'Pull events from venues, performers, and other curators', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            ['Approval Workflow', 'Review and approve events before publishing', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            // Careful wording, and it has to stay careful: the automatic digest
                            // (app:send-event-announcements) reaches confirmed EMAIL SUBSCRIBERS -
                            // role_subscribers rows, captured by the subscribe panel and the
                            // checkout opt-in. Account followers (role_user at level 'follower')
                            // are reached only by a newsletter the owner composes and sends. So
                            // "subscribers", never "followers", in any sentence about automatic mail.
                            ['Build Your Audience', 'Subscribers hear when you publish, with no algorithm in between', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ];
                    @endphp
                    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($curatorTiles as [$tileTitle, $tileBody, $tilePath])
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-white/5">
                                <span class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tilePath }}" /></svg>
                                </span>
                                <h4 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $tileTitle }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $tileBody }}</p>
                            </div>
                        @endforeach
                    </div>

                    <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 transition-all group-hover:gap-2.5 dark:text-emerald-400">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </div>
                <div class="es-glare"></div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Online Events                                               -->
    <!-- ============================================================ -->
    <section id="online" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 dark:bg-cyan-500/20">
                        <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">For Online Events</h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">Webinars, classes, conferences, and watch parties that happen on a screen instead of in a room. Add your streaming link to any event and attendees get it on their ticket. <a href="{{ marketing_url('/features/online-events') }}" class="font-medium text-cyan-700 hover:underline dark:text-cyan-400">See how online events work</a>.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="55">
                @foreach ($online as $a)
                    <x-marketing.audience-card
                        :url="marketing_url($a['url'])"
                        :name="$a['name']"
                        :blurb="$a['blurb']"
                        accent="cyan"
                        :tags="$a['tags']">
                        <x-slot name="icon">{!! $a['icon'] !!}</x-slot>
                    </x-marketing.audience-card>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Developers & AI Agents                                      -->
    <!-- ============================================================ -->
    <section id="developers" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-500/20">
                        <svg class="h-6 w-6 text-slate-600 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">For Developers &amp; AI Agents</h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">Every schedule publishes a public calendar feed, and on Pro it is also a REST API and a set of signed webhooks. Agents can discover all of it on their own and run multi-step flows without anyone wiring them up first.</p>
                <a href="{{ marketing_url('/for-ai-agents') }}"
                   class="group mt-5 inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold ring-1 transition-all text-slate-700 ring-slate-300 hover:bg-slate-100 dark:text-slate-300 dark:ring-slate-400/30 dark:hover:bg-slate-500/10">
                    See the full developer guide
                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            {{-- Live request. es-band-dark is fixed-dark in both themes, the same treatment
                 /for-ai-agents uses for its quickstart. --}}
            <div class="es-band-dark noise relative mb-6 overflow-hidden rounded-[2.5rem] border border-white/[0.06] p-6 sm:p-8 lg:p-10" data-reveal="panel">
                <div class="mb-5 flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FF5F57;"></span>
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FEBC2E;"></span>
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #28C840;"></span>
                    <span dir="ltr" class="ms-3 font-mono text-[11px] text-gray-400">GET /api/events</span>
                </div>
                <pre dir="ltr" class="overflow-x-auto font-mono text-[12px] leading-relaxed text-gray-300 sm:text-[13px]"><code><span class="text-emerald-400">$ curl https://eventschedule.com/api/events \
    -H "X-API-Key: $EVENTSCHEDULE_KEY"</span>

{
  "data": [
    {
      "id": "8Q2Kx",
      "name": "Jazz Night",
      "starts_at": "2026-03-15 20:00:00",
      "venue_name": "Blue Note",
      "url": "https://bluenote.eventschedule.com/jazz-night/8Q2Kx"
    }
  ],
  "meta": { "per_page": 100, "total": 42 }
}</code></pre>
                <p class="mt-5 text-xs text-gray-400">300 read / 30 write requests per minute &middot; offset pagination &middot; the same API on selfhosted installs</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="55">
                <x-marketing.audience-card
                    :url="marketing_url('/docs/developer/api#authentication')"
                    name="REST API"
                    blurb="Create schedules, events, tickets and sales from your own code. JSON in, JSON out, on Pro and on every selfhost install."
                    accent="slate"
                    :external="false"
                    :tags="['Schedules', 'Events', 'Sales', 'Categories', 'Feedback', 'Sub-schedules']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></x-slot>
                </x-marketing.audience-card>
                <x-marketing.audience-card
                    :url="marketing_url('/docs/developer/webhooks#event-types')"
                    name="Webhooks"
                    blurb="Get a signed POST the moment a ticket sells, an event changes, or someone scans in at the door."
                    accent="slate"
                    :external="false"
                    :tags="['HMAC-SHA256 signed', 'Automatic retries', 'Delivery log']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></x-slot>
                </x-marketing.audience-card>
                <x-marketing.audience-card
                    :url="url('/api/openapi.json')"
                    name="OpenAPI spec"
                    blurb="Every operation described in OpenAPI 3.0.3, so you can generate a typed client in your language."
                    accent="slate"
                    :external="true"
                    :tags="['26 operations', 'Generate a client', 'Machine readable']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></x-slot>
                </x-marketing.audience-card>
                <x-marketing.audience-card
                    :url="marketing_url('/for-ai-agents')"
                    name="Built for AI agents"
                    blurb="Agents discover the API on their own and run multi-step flows without a human wiring them up."
                    accent="slate"
                    :external="false"
                    :tags="['llms.txt', 'llms-full.txt', 'agents.json', 'Named flows']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></x-slot>
                </x-marketing.audience-card>
                <x-marketing.audience-card
                    :url="marketing_url('/docs/sharing#calendar-feeds')"
                    name="Calendar feeds"
                    blurb="Every schedule publishes a public iCal and RSS feed. No auth, no tokens, just a URL to subscribe to."
                    accent="slate"
                    :external="false"
                    :tags="['iCal', 'RSS', 'No auth needed', 'Auto-updating']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></x-slot>
                </x-marketing.audience-card>
                <x-marketing.audience-card
                    :url="marketing_url('/features/embed-calendar')"
                    name="Embed anywhere"
                    blurb="Drop your calendar or a ticket checkout into any site with a single iframe."
                    accent="slate"
                    :external="false"
                    :tags="['Calendar embed', 'Ticket widget', 'One iframe']">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" /></x-slot>
                </x-marketing.audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Frequently asked <span class="text-gradient-usecases">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal>
                    Common questions about Event Schedule.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Whatever you run, <span class="text-gradient-usecases">start free</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your schedule in seconds. No credit card, no platform fees, ever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = [
            ['top', 'Top'],
            ['performers', 'Performers'],
            ['venues', 'Venues'],
            ['curators', 'Curators'],
            ['online', 'Online'],
            ['developers', 'Developers'],
            ['claim', 'Get started'],
        ];
    @endphp
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
