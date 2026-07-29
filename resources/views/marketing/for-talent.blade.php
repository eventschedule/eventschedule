<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Performers & Artists</x-slot>
    <x-slot name="description">Free event scheduling for performers and artists. Share your shows, sell tickets with zero platform fees, and let venues add you to their own schedules.</x-slot>
    <x-slot name="breadcrumbTitle">For Talent</x-slot>

    @php
        // The same twelve cards render on /use-cases. Shared so a blurb edited in
        // one place can never leave the other stale.
        $performers = config('marketing_audiences.performers');

        // Proof rail. Only shown at a size that fills its grid completely: 8 (two
        // rows of four) or 4 (one row). Below four real shows it is not proof, so
        // the whole section stands down rather than look thin.
        $railCount = $talentEvents->count();
        $rail = $railCount >= 8 ? $talentEvents->take(8) : ($railCount >= 4 ? $talentEvents->take(4) : collect());
        $hasRail = $rail->isNotEmpty();

        // Fee math mirrors marketing/compare.blade.php:356-357 and the /pricing
        // calculator verbatim, so the three pages can never quote different totals.
        // Eventbrite's 3.7% + $1.79 bundles payment processing; ours does not, so
        // the Stripe fee is shown on our side rather than claimed as zero.
        $proMonthly = (int) config('services.stripe_platform.price_monthly_amount', 5);
        $feeTickets = 200;
        $feePrice = 25;
        $feeRevenue = $feeTickets * $feePrice;
        $feeStripe = ($feeRevenue * 0.029) + ($feeTickets * 0.30);
        $feeEs = $proMonthly + $feeStripe;
        $feeEb = ($feeRevenue * 0.037) + ($feeTickets * 1.79);
        $feeKeep = $feeEb - $feeEs;

        // Decorative only: the twelve linked audiences sit directly below it.
        $performerTypes = ['Musicians', 'DJs', 'Comedians', 'Dancers', 'Magicians', 'Poets', 'Acrobats', 'Actors', 'Bands', 'Instructors', 'Artists', 'Vendors'];

        $faqs = [
            ['q' => 'Is Event Schedule free for performers?', 'a' => 'Yes. Sharing your show schedule, syncing your calendar, taking booking requests from venues, and letting fans follow you are all free forever, and so is a newsletter allowance of 10 emails each month, counted per recipient rather than per send. Ticketing, event graphics and a larger newsletter allowance are on the Pro plan at $' . $proMonthly . '/month, and there are still no platform fees on ticket sales.'],
            ['q' => 'What happens when a venue books me for a show?', 'a' => 'The venue adds you to their event and you get a request. Accept it and the gig appears on your schedule automatically, with the venue listed on it. You never type the same date into two calendars, and both schedules stay in sync from then on.'],
            ['q' => 'I already have a Linktree. Why would I need this?', 'a' => 'A link page shows buttons. A schedule shows dates. Your Event Schedule page lists your actual upcoming shows with venues, times and ticket links, updates itself as you add dates, and lets fans follow you for an email when a new show lands. You can keep your link page and point it here, or replace it entirely.'],
            ['q' => 'Can I put my dates on my own website and social profiles?', 'a' => 'Yes. Embed your schedule on any website with a single iframe, or share your schedule URL on social profiles, EPKs and booking platforms. There are also iCal and RSS feeds, so your dates can flow into other calendars and sites automatically. Everything updates the moment you add a show.'],
            ['q' => 'How do fans find out about my upcoming shows?', 'a' => 'Fans follow your schedule and get an email when you add new dates. On Pro you can also send newsletters with your upcoming shows, generate a shareable graphic for each event, and boost events with Meta Ads.'],
        ];
    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Performers & Artists",
        "description": "Free event scheduling for performers and artists of every kind. Share your shows, sell tickets, sync with Google Calendar, and let venues add you to their schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Performers & Artists"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Performers & Artists",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Performer Scheduling Software",
        "operatingSystem": "Web",
        "description": "Free event scheduling for performers and artists. Share your shows, sell tickets, sync with Google Calendar, and let venues add you to their schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Custom schedule URL",
            "Google Calendar, Outlook and CalDAV sync",
            "Venue booking requests",
            "Embeddable calendar",
            "iCal and RSS feeds",
            "Fan photos, videos and comments on events"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "performer schedule, share tour dates, artist event calendar, performer booking, gig management, free event scheduling",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Event Schedule for every kind of performer",
        "numberOfItems": {{ count($performers) }},
        "itemListElement": [
            @foreach ($performers as $i => $p)
            {
                "@type": "ListItem",
                "position": {{ $i + 1 }},
                "name": {!! json_encode($p['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
                "url": {!! json_encode(marketing_url($p['url']), JSON_UNESCAPED_SLASHES) !!}
            }@if (! $loop->last),@endif
            @endforeach
        ]
    }
    </script>
    </x-slot>

    <x-seo.faq-schema :items="$faqs" />

    <style {!! nonce_attr() !!}>
        /* For-talent "Center Stage" styles - the accent gradient only. Everything
           else on this page comes from the shared es-* system in marketing.css. */
        .text-gradient-talent {
            background-image: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-talent,
        .es-band-dark .text-gradient-talent,
        .es-finale-panel .text-gradient-talent {
            background-image: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
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
    <!-- 1. Hero: your schedule page                                  -->
    <!-- ============================================================ -->
    {{-- Text-only hero, sized like the other rebuilt pages: the sections below are
         the payoff, so the hero stays compact instead of holding a mockup. --}}
    <section id="top" class="es-hero relative flex items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise lg:min-h-[calc(58svh-4rem)] lg:py-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(37, 99, 235, 0.32), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 38%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 62%, rgba(6, 182, 212, 0.18), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Performers &amp; Artists of Every Kind</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.7rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Every show you play,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-talent es-gradient-anim">on one link</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-8 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Musicians, comedians, DJs, dancers, magicians and more. Venues add you to their lineup, your dates land on your page automatically, and fans get told. Free forever, and you can <a href="{{ marketing_url('/features/ticketing') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">sell tickets</a> with zero platform fees.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    <span class="relative z-10 flex items-center gap-2">
                        Create your schedule
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                    <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                </a>
                <a href="#gigs" class="group inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
            </div>

            {{-- Mobile wayfinding: the dot nav is lg-only, so without these there is
                 no way to skip ahead on a phone. --}}
            <nav class="es-fade-up es-d-4 mt-7 flex flex-wrap items-center justify-center gap-2 lg:hidden" aria-label="Jump to a section">
                @if ($hasRail)
                    <a href="#live" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">Live now</a>
                @endif
                <a href="#gigs" class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3.5 py-1.5 text-sm font-medium text-sky-700 transition-colors hover:bg-sky-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-sky-400/30 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:bg-sky-500/20">How it works</a>
                <a href="#keep" class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3.5 py-1.5 text-sm font-medium text-cyan-700 transition-colors hover:bg-cyan-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-cyan-400/30 dark:bg-cyan-500/10 dark:text-cyan-300 dark:hover:bg-cyan-500/20">Fees</a>
                <a href="#stages" class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">Your stage</a>
            </nav>

        </div>

        <div class="es-spot pointer-events-none absolute inset-0 z-[3]" aria-hidden="true"></div>
    </section>

    @if ($hasRail)
        <!-- ============================================================ -->
        <!-- 2. Proof: real shows, listed right now                       -->
        <!-- ============================================================ -->
        <section id="live" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
                    <div class="max-w-2xl">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75 motion-safe:animate-ping"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                            </span>
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Live now</span>
                        </div>
                        <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                            Shows performers are <span class="text-gradient-talent">listing today</span>
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400">Real upcoming dates from talent schedules on Event Schedule. Every one of these is somebody's page, running on the free plan or better.</p>
                    </div>
                    <a href="{{ url('/browse') }}" class="group inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-blue-700 ring-1 ring-blue-200 transition-all hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-blue-300 dark:ring-blue-400/30 dark:hover:bg-blue-500/10">
                        Browse all events
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4" data-reveal-group="70">
                    @foreach ($rail as $railEvent)
                        <a href="{{ $railEvent->getGuestUrl() }}" data-reveal data-tilt="2.5"
                           class="es-bento es-tilt-inner group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="aspect-[3/4] w-full overflow-hidden bg-gray-100 dark:bg-white/5">
                                <img src="{{ $railEvent->getImageUrl() }}" alt="{{ $railEvent->name }}" loading="lazy" decoding="async"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            </div>
                            <div class="flex flex-1 flex-col p-3.5">
                                <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">
                                    {{ $railEvent->starts_at ? $railEvent->getShortDateRangeDisplay('M j') : __('messages.recurring') }}
                                </div>
                                <h3 class="line-clamp-2 text-sm font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $railEvent->name }}</h3>
                            </div>
                            <div class="es-glare"></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ============================================================ -->
    <!-- 3. Life of a gig: header + five banners                      -->
    <!-- ============================================================ -->
    <section id="gigs" class="scroll-mt-24 bg-white pb-4 pt-16 dark:bg-[#0a0a0f] lg:pb-6 lg:pt-24">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]" aria-hidden="true"></span>
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Life of a gig</span>
            </div>
            <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                From the booking to the <span class="text-gradient-talent">encore</span>
            </h2>
            <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                One date, followed all the way through. Everything free unless a step says otherwise.
            </p>
        </div>
    </section>

    {{-- 3a. Get booked --}}
    <x-marketing.feature-banner
        {{-- No /features page covers booking requests; the user guide section does. --}}
        :href="marketing_url('/docs/managing-schedules') . '#requests'"
        accent="blue"
        badge="Get booked"
        heading="Venues add you. You just say yes."
        lede="A venue building their lineup adds you to the bill and you get a request. Accept it and the gig lands on your schedule with the venue attached. No double entry, no chasing anyone for the details."
        :chips="['Booking requests, free', 'Both schedules stay in sync', 'Custom request fields, Pro']"
        :lead="true"
        ground="white"
        frame="panel">
        <div class="space-y-3">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3.5 dark:border-blue-400/25 dark:bg-blue-500/10">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-[10px] font-black text-white">TL</span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-bold text-gray-900 dark:text-white">The Lantern</div>
                        <div class="truncate text-[11px] text-gray-500 dark:text-gray-400">wants to add you to a show</div>
                    </div>
                </div>
                <div class="mb-3 rounded-lg bg-white/70 px-2.5 py-2 text-[11px] text-gray-600 dark:bg-black/20 dark:text-gray-300">
                    Fri 12 Sep &middot; 21:00 &middot; Portland, OR &middot; Support slot
                </div>
                <div class="flex gap-2">
                    <span class="flex-1 rounded-lg bg-blue-600 py-1.5 text-center text-[11px] font-semibold text-white">Accept</span>
                    <span class="flex-1 rounded-lg border border-gray-300 py-1.5 text-center text-[11px] font-semibold text-gray-600 dark:border-white/15 dark:text-gray-300">Decline</span>
                </div>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 dark:border-emerald-400/25 dark:bg-emerald-500/10">
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <span class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">On your schedule and theirs</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    {{-- 3b. Announce it once --}}
    <x-marketing.feature-banner
        :href="marketing_url('/features/embed-calendar')"
        accent="sky"
        badge="Announce it once"
        heading="Add the date once. It shows up everywhere."
        lede="Your schedule page, your own website, your followers' calendars and any app that reads a feed. Add a show in one place and every one of them updates, including the calendar you already live in."
        :chips="['Website embed', 'Google, Outlook, CalDAV', 'iCal and RSS feeds', 'Free']"
        :flip="true"
        ground="gray"
        frame="browser"
        frameUrl="yourband.com/tour">
        <div>
            <div class="mb-3 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-900 dark:text-white">Tour dates</span>
                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">Embedded</span>
            </div>
            <div class="space-y-1.5">
                @foreach ([['Sep 12', 'The Lantern'], ['Sep 20', 'Blue Room'], ['Oct 02', 'Open Mic Night']] as [$d, $v])
                    <div class="flex items-center gap-2.5 rounded-lg border border-gray-200 px-2.5 py-1.5 dark:border-white/10">
                        <span class="w-12 shrink-0 text-[10px] font-bold text-sky-600 dark:text-sky-400">{{ $d }}</span>
                        <span class="min-w-0 flex-1 truncate text-[11px] text-gray-700 dark:text-gray-300">{{ $v }}</span>
                        <span class="shrink-0 text-[10px] text-gray-400">Tickets</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach (['Google', 'Outlook', 'CalDAV', 'iCal', 'RSS'] as $sync)
                    <span class="rounded-md border border-gray-200 px-1.5 py-0.5 text-[9px] font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">{{ $sync }}</span>
                @endforeach
            </div>
        </div>
    </x-marketing.feature-banner>

    {{-- 3c. Sell the tickets --}}
    <x-marketing.feature-banner
        :href="marketing_url('/features/ticketing')"
        accent="cyan"
        badge="Sell the tickets"
        heading="Take the door yourself. Keep all of it."
        lede="Sell straight from your event page through your own Stripe account. Buyers get a QR ticket, you scan them in at the door, and the money lands with you. We never take a cut of a ticket, on any plan."
        :chips="['Zero platform fees', 'QR check-in', 'Season and visit passes', 'Pro']"
        ground="white"
        frame="phone">
        <div>
            <div class="rounded-xl bg-gradient-to-br from-blue-600 to-cyan-600 p-3 text-white">
                <div class="mb-0.5 text-[10px] uppercase tracking-wide opacity-80">Your Band</div>
                <div class="mb-2 text-xs font-black">The Lantern &middot; Fri 12</div>
                <div class="rounded-lg bg-white p-2">
                    <div class="grid grid-cols-6 gap-0.5" aria-hidden="true">
                        @foreach ([1,0,1,1,0,1, 0,1,1,0,1,0, 1,1,0,1,0,1, 0,1,0,1,1,0, 1,0,1,0,1,1, 1,1,0,1,0,1] as $bit)
                            <span class="aspect-square rounded-[1px] {{ $bit ? 'bg-gray-900' : 'bg-white' }}"></span>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[10px]">
                    <span class="opacity-80">General admission</span>
                    <span class="font-bold">$15.00</span>
                </div>
            </div>
            <div class="mt-2.5 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-2 dark:border-emerald-400/25 dark:bg-emerald-500/10">
                <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <span class="text-[10px] font-semibold text-emerald-800 dark:text-emerald-300">Checked in &middot; 21:04</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    {{-- 3d. Fill the room --}}
    <x-marketing.feature-banner
        :href="marketing_url('/features/newsletters')"
        accent="emerald"
        badge="Fill the room"
        heading="Tell the people who already said yes."
        lede="Fans who follow you get an email the moment a date lands. Write a newsletter when you want to say more, and let Event Schedule generate the poster so you have something to post the same afternoon."
        :chips="['Follower emails, free', 'Newsletters', 'Event graphics, Pro', 'Meta Ads boost, Pro']"
        :flip="true"
        ground="gray"
        frame="panel">
        <div class="space-y-3">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/[0.04]">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-900 dark:text-white">New dates just added</span>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-semibold text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">1,284 followers</span>
                </div>
                <div class="space-y-1.5" aria-hidden="true">
                    <span class="block h-1.5 w-full rounded-full bg-gray-200 dark:bg-white/10"></span>
                    <span class="block h-1.5 w-11/12 rounded-full bg-gray-200 dark:bg-white/10"></span>
                    <span class="block h-1.5 w-7/12 rounded-full bg-gray-200 dark:bg-white/10"></span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-cyan-600 text-white">
                    <span class="text-[8px] uppercase tracking-wide opacity-80">Fri</span>
                    <span class="text-lg font-black leading-none">12</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[11px] font-semibold text-gray-900 dark:text-white">Poster generated</div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Square, story and banner sizes</div>
                </div>
            </div>
        </div>
    </x-marketing.feature-banner>

    {{-- 3e. After the show --}}
    <x-marketing.feature-banner
        :href="marketing_url('/features/analytics')"
        accent="amber"
        badge="After the show"
        heading="The night doesn't end at load-out."
        lede="People who were there post photos and video to the event page, and nothing goes live until you approve it. Ratings tell you how it went, and your analytics tell you which venues and which cities actually turn up."
        :chips="['Fan photos and video', 'Approval queue', 'Analytics', 'Post-event ratings, Pro']"
        ground="white"
        frame="panel">
        <div class="space-y-3">
            <div class="flex gap-1.5" aria-hidden="true">
                <span class="h-12 flex-1 rounded-lg bg-gradient-to-br from-blue-400 to-cyan-500"></span>
                <span class="h-12 flex-1 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500"></span>
                <span class="h-12 flex-1 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500"></span>
                <span class="flex h-12 flex-1 items-center justify-center rounded-lg border border-dashed border-gray-300 text-[10px] font-semibold text-gray-400 dark:border-white/15">+9</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex gap-0.5" aria-hidden="true">
                    @for ($s = 0; $s < 5; $s++)
                        <svg class="h-3.5 w-3.5 {{ $s < 4 ? 'text-amber-500' : 'text-gray-300 dark:text-white/20' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.364 1.118l1.287 3.958c.3.921-.755 1.688-1.539 1.118l-3.366-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.197-1.538-1.118l1.286-3.958a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.951-.69l1.286-3.958z" /></svg>
                    @endfor
                </span>
                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">4.6 from 38 ratings</span>
            </div>
            <div class="space-y-1.5">
                @foreach ([['Portland', 'w-full'], ['Seattle', 'w-8/12'], ['Eugene', 'w-5/12']] as [$city, $w])
                    <div class="flex items-center gap-2">
                        <span class="w-14 shrink-0 text-[10px] text-gray-500 dark:text-gray-400">{{ $city }}</span>
                        <span class="h-1.5 flex-1 rounded-full bg-gray-100 dark:bg-white/10">
                            <span class="block h-1.5 {{ $w }} rounded-full bg-gradient-to-r from-blue-500 to-cyan-500"></span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-marketing.feature-banner>

    <!-- ============================================================ -->
    <!-- 4. What you keep (dark band)                                 -->
    <!-- ============================================================ -->
    <section id="keep" class="relative scroll-mt-24 bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 25%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">The door money</span>
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        A sold-out night, <span class="text-gradient-talent">two invoices</span>
                    </h2>
                    <p class="text-lg text-gray-400" data-reveal style="--reveal-delay: 0.14s;">
                        {{ $feeTickets }} tickets at ${{ $feePrice }}, so ${{ number_format($feeRevenue) }} through the door. Here is what each platform takes.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="110">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Eventbrite</div>
                        <div class="mb-2 text-4xl font-black text-white">${{ number_format($feeEb, 2) }}</div>
                        <p class="text-sm text-gray-400">3.7% + $1.79 per ticket, with payment processing bundled in.</p>
                    </div>

                    <div class="rounded-2xl border border-cyan-400/30 bg-cyan-500/10 p-6 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300">Event Schedule</div>
                        <div class="mb-2 text-4xl font-black text-white">${{ number_format($feeEs, 2) }}</div>
                        <p class="text-sm text-gray-400">${{ $proMonthly }} for Pro plus Stripe's 2.9% + $0.30. Our platform fee is $0.</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">You keep</div>
                        <div class="mb-2 text-4xl font-black text-gradient-talent">${{ number_format($feeKeep, 2) }}</div>
                        <p class="text-sm text-gray-400">More, on one night. Roughly {{ floor($feeKeep / $feePrice) }} extra tickets you didn't have to sell.</p>
                    </div>
                </div>

                {{-- gray-400, not gray-500: small text on the dark band needs 4.5:1. --}}
                <p class="mx-auto mt-8 max-w-3xl text-center text-sm text-gray-400" data-reveal>
                    Card processing is real either way. Eventbrite folds it into their rate; we show Stripe's separately and charge nothing on top, so the comparison is like for like.
                    <a href="{{ marketing_url('/pricing') }}#fees" class="ms-1 font-medium text-cyan-300 hover:underline">Run your own numbers</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Free vs Pro for performers                                -->
    <!-- ============================================================ -->
    <section id="plans" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    What you get <span class="text-gradient-talent">free</span>, and what costs ${{ $proMonthly }}
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Most performers never need to pay. The line is drawn at selling and promoting.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="flex flex-col rounded-3xl border border-gray-200 bg-gray-50 p-7 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <div class="mb-1 text-sm font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Free forever</div>
                    <div class="mb-5 text-4xl font-black text-gray-900 dark:text-white">$0</div>
                    {{-- Hardcoded markup, no user data, so the inline links are safe to unescape. --}}
                    <ul class="mb-6 space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                        @foreach ([
                            'Unlimited events on your own schedule URL',
                            'Booking requests from venues and curators',
                            '<a href="' . marketing_url('/features/calendar-sync') . '" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Google Calendar, Outlook and CalDAV sync</a>',
                            'Website embed, iCal and RSS feeds',
                            '<a href="' . marketing_url('/features/online-events') . '" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Online and hybrid shows</a> with a streaming link',
                            'Followers, with an email when you add dates',
                            'Fan photos, videos and comments, all approved by you',
                        ] as $freeItem)
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{!! $freeItem !!}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ app_url('/sign_up?type=talent') }}" class="group mt-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-300 px-4 py-3 text-base font-semibold text-gray-800 transition-all hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/15 dark:text-white">
                        Start free
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>

                <div class="flex flex-col rounded-3xl border border-blue-200 bg-blue-50/50 p-7 dark:border-blue-400/25 dark:bg-blue-500/[0.07]" data-reveal="panel">
                    <div class="mb-1 text-sm font-semibold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Pro</div>
                    <div class="mb-5 flex items-baseline gap-1.5">
                        <span class="text-4xl font-black text-gray-900 dark:text-white">${{ $proMonthly }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">/month</span>
                    </div>
                    <ul class="mb-6 space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                        @foreach ([
                            'Sell tickets with QR check-in and zero platform fees',
                            'Season passes, visit passes and promo codes',
                            '100 newsletter recipients a month, up from 10',
                            'Auto-generated event graphics for socials',
                            'Remove Event Schedule branding',
                            'Boost events with Meta Ads',
                        ] as $proItem)
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ $proItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ marketing_url('/pricing') }}" class="group mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">
                        See all plans
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>
                Running a band or a company? Enterprise adds multiple team members, whole-day availability tracking and a custom domain.
                <a href="{{ marketing_url('/selfhost') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Selfhost</a> and every one of those is free.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Find your stage                                           -->
    <!-- ============================================================ -->
    <section id="stages" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        {{-- Decorative: the twelve linked audiences directly below say the same
             thing with names and links, so the marquee is aria-hidden in full.
             Under reduced motion the shared CSS wraps this into a static list. --}}
        <div class="mb-12 es-marquee-mask" aria-hidden="true">
            <div class="es-marquee" data-marquee="1">
                <div class="es-marquee-track">
                    @for ($tc = 0; $tc < 2; $tc++)
                        @foreach ($performerTypes as $tag)
                            <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-white px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]"></span>
                                {{ $tag }}
                            </span>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/20">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Find your kind of stage</h2>
                    <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">{{ count($performers) }} pages</span>
                </div>
                <p class="mb-5 text-lg text-gray-600 dark:text-gray-400">The same schedule underneath, written for how you actually work. Pick the one that sounds like your week.</p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ marketing_url('/for-venues') }}" class="group inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200 transition-all hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-amber-300 dark:ring-amber-400/30 dark:hover:bg-amber-500/10">
                        Not a performer? See venues
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                    <a href="{{ marketing_url('/for-curators') }}" class="group inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200 transition-all hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-emerald-300 dark:ring-emerald-400/30 dark:hover:bg-emerald-500/10">
                        Booking a lineup? See curators
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">
                @foreach ($performers as $p)
                    <x-marketing.audience-card
                        :url="marketing_url($p['url'])"
                        :name="$p['name']"
                        :blurb="$p['blurb']"
                        accent="blue"
                        :tags="$p['tags']">
                        <x-slot name="icon">{!! $p['icon'] !!}</x-slot>
                    </x-marketing.audience-card>
                @endforeach
            </div>

            <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>
                Not on the list? It makes no difference. Every schedule works the same way, whatever you call what you do.
                <a href="{{ marketing_url('/use-cases') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">See all use cases</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Frequently asked <span class="text-gradient-talent">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal>
                    Everything performers ask before they move their dates over.
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
    <!-- 8. Finale                                                    -->
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
                        Claim the link you'll <span class="text-gradient-talent">put in your bio</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Your next show is already booked. Give people somewhere to find it.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started for free
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

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = array_values(array_filter([
            ['top', 'Top'],
            $hasRail ? ['live', 'Live now'] : null,
            ['gigs', 'How it works'],
            ['keep', 'Fees'],
            ['plans', 'Plans'],
            ['stages', 'Your stage'],
            ['claim', 'Get started'],
        ]));
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
