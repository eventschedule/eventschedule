<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Venues | Manage Your Event Calendar</x-slot>
    <x-slot name="description">Run your venue calendar front to back. Accept booking requests, sell tickets with QR check-in, and give every room and stage its own sub-schedule. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Venues</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Venues",
        "description": "Run your venue calendar front to back. Accept booking requests, sell tickets with QR check-in, take private hire bookings, and give every room and stage its own sub-schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Venues"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Venues",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Venue Management Software",
        "operatingSystem": "Web",
        "description": "Run your venue calendar front to back. Accept booking requests, sell tickets with QR check-in, take private hire bookings, and give every room and stage its own sub-schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Public event calendar",
            "Booking request inbox",
            "QR code ticketing and check-in dashboard",
            "Promo codes and gift cards",
            "Private hire booking",
            "Sub-schedules for rooms and stages",
            "Custom domain and white label",
            "Team management",
            "Google Calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "venue event calendar, venue booking management, venue schedule software, event space calendar, free venue scheduling",
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

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For-venues "Front of House" styles.

           The page is one building drawn twice: an elevation out front
           (what your audience sees) and a floor plan out back (what your
           staff runs). Same thin line language both times, so the pass
           door between the two acts reads as walking through a doorway
           rather than landing on a different site.

           The shared es-* system (aurora, reveals, bento, marquee, bands,
           finale) lives in marketing.css; only this page's own colour
           identity and motifs live here. Nothing blinks: the front-of-house
           lights pan slowly, the back-of-house work light does not move at
           all, and that stillness is the point.
           ============================================================== */

        /* House accent. Light mode uses deeper sky/cyan so headings stay
           legible on white; dark mode lightens them. */
        .text-gradient-house {
            background-image: linear-gradient(135deg, #0284c7, #0891b2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-house { background-image: linear-gradient(135deg, #38bdf8, #22d3ee); }
        /* Fixed-dark surfaces are dark in BOTH colour modes, so they need the
           light stops unconditionally. `background-image` rather than the
           `background` shorthand on purpose: the shorthand resets
           background-clip and the gradient renders as a solid block. */
        .es-band-dark .text-gradient-house,
        .es-finale-panel .text-gradient-house { background-image: linear-gradient(135deg, #7dd3fc, #67e8f9); }

        /* Shared odometer digits carry a hard-coded brand-blue gradient in
           marketing.css, because translateY breaks background-clip inheritance. */
        .es-od-strip span { background-image: linear-gradient(135deg, #0284c7, #0891b2); }
        .dark .es-od-strip span { background-image: linear-gradient(135deg, #38bdf8, #22d3ee); }

        /* ---- Front of house: the lights are on and slightly alive ---- */
        .es-foh-light {
            position: absolute;
            top: -12%;
            width: 46%;
            height: 135%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-foh-pan 11s ease-in-out infinite alternate;
        }
        .es-foh-light-1 { left: 3%; background: conic-gradient(from 197deg at 50% 0%, transparent 0deg, rgba(56, 189, 248, 0.16) 11deg, transparent 24deg); }
        .es-foh-light-2 { right: 3%; background: conic-gradient(from 149deg at 50% 0%, transparent 0deg, rgba(34, 211, 238, 0.14) 11deg, transparent 24deg); animation-delay: -4s; animation-duration: 13s; }
        .es-foh-light-3 { left: 33%; background: conic-gradient(from 178deg at 50% 0%, transparent 0deg, rgba(14, 165, 233, 0.12) 9deg, transparent 20deg); animation-delay: -7s; animation-duration: 15s; }
        @keyframes es-foh-pan { from { transform: rotate(-7deg); } to { transform: rotate(7deg); } }

        /* ---- Back of house: one bare, cold, motionless work light ---- */
        .es-foh-worklight {
            position: absolute;
            top: -8%;
            left: 50%;
            width: 76%;
            height: 128%;
            margin-left: -38%;
            pointer-events: none;
            background: conic-gradient(from 171deg at 50% 0%, transparent 0deg, rgba(203, 213, 225, 0.07) 9deg, transparent 18deg);
        }

        /* ---- The pass door: the seam where the elevation becomes the plan ----
           The top stop must match the ground of the section immediately above
           (the "Also out front" grid), or the seam shows as a faint band. */
        .es-foh-fade { background-image: linear-gradient(180deg, #ffffff 0%, rgba(5, 5, 8, 0) 100%); }
        .dark .es-foh-fade { background-image: linear-gradient(180deg, #0a0a0f 0%, rgba(5, 5, 8, 0) 100%); }

        /* ---- ACCEPTED stamp on the booking-inbox mock ---- */
        .es-foh-stamp {
            transform: rotate(-9deg);
            border: 2px solid rgba(5, 150, 105, 0.6);
            color: #059669;
            background-color: rgba(16, 185, 129, 0.08);
            letter-spacing: 0.14em;
        }
        .dark .es-foh-stamp {
            border-color: rgba(52, 211, 153, 0.55);
            color: #34d399;
            background-color: rgba(16, 185, 129, 0.12);
        }

        @media (prefers-reduced-motion: reduce) {
            .es-foh-light { animation: none !important; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // One week at a mixed-programme room. Deliberately not all nightlife:
        // this page also serves libraries, markets and community centers.
        // ------------------------------------------------------------------
        $week = [
            ['day' => 'Mon', 'name' => null],
            ['day' => 'Tue', 'name' => 'Quiz Night', 'tag' => 'Weekly', 'tone' => 'quiet'],
            ['day' => 'Wed', 'name' => 'Life Drawing', 'tag' => 'Weekly', 'tone' => 'quiet'],
            ['day' => 'Thu', 'name' => 'Open Mic', 'tag' => 'Weekly', 'tone' => 'quiet'],
            ['day' => 'Fri', 'name' => 'The Rialtos', 'tag' => 'Tickets', 'tone' => 'loud'],
            ['day' => 'Sat', 'name' => 'Soul Night', 'tag' => 'Tickets', 'tone' => 'loud'],
            ['day' => 'Sun', 'name' => 'Makers Market', 'tag' => 'Monthly', 'tone' => 'quiet'],
        ];

        // Twelve venue types, shared with /use-cases via config so a blurb edited
        // on one page cannot silently drift from the other.
        $venueTypes = config('marketing_audiences.venues', []);

        // ------------------------------------------------------------------
        // The tools a venue is usually running before it consolidates. Each
        // one has a real replacement page, so the "before" column is also the
        // page's internal-link surface.
        // ------------------------------------------------------------------
        $replaces = [
            ['tool' => 'A form for band submissions', 'url' => '/google-forms-replacement', 'now' => 'A booking inbox that files itself'],
            ['tool' => 'A spreadsheet for the lineup', 'url' => '/google-sheets-replacement', 'now' => 'A calendar the public can read'],
            ['tool' => 'A mailing list tool', 'url' => '/mailchimp-replacement', 'now' => 'Followers who opted in on your page'],
            ['tool' => 'A flyer designer', 'url' => '/canva-replacement', 'now' => 'Event graphics generated per show'],
            ['tool' => 'A link-in-bio page', 'url' => '/linktree-replacement', 'now' => 'One address that is always current'],
            ['tool' => 'A booking link for hire enquiries', 'url' => '/calendly-replacement', 'now' => 'Bookable spaces on the same schedule'],
            ['tool' => 'A QR code generator', 'url' => '/qr-code-generator-replacement', 'now' => 'A QR code on every ticket'],
        ];

        // $proMonthly / $entMonthly come from the marketing.* view composer.

        $plans = [
            [
                'name' => 'Free',
                'price' => plan_price(0),
                'note' => 'forever',
                'lede' => 'Everything you need to get the room listed, taking requests and selling its first tickets.',
                'items' => ['Public event calendar', 'Booking request inbox', '25 paid tickets a month, QR scanning included', 'Sub-schedules for each room', 'Recurring nights', 'Google, Outlook and CalDAV sync', 'Free RSVPs and analytics'],
                'featured' => false,
            ],
            [
                'name' => 'Pro',
                'price' => plan_price($proMonthly),
                'note' => 'per month',
                'lede' => 'Adds the box office and the things that make it look like yours.',
                'items' => ['Everything in Free', 'Unlimited ticket sales', 'Live check-in dashboard', 'Promo codes, gift cards and waitlists', 'Unlimited bookable spaces', 'No Event Schedule branding'],
                'featured' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => plan_price($entMonthly),
                'note' => 'per month',
                'lede' => 'For rooms with a real team and a brand of their own.',
                'items' => ['Everything in Pro', 'Your own domain', 'Up to five team members', 'Internal and unlisted events', '1,000 newsletter recipients a month', 'Priority support'],
                'featured' => false,
            ],
        ];

        $howToSteps = [
            ['name' => 'Create your schedule', 'text' => 'Pick a name and a web address for your venue. You get a public calendar page in about a minute, with no credit card.'],
            ['name' => 'Add your first events', 'text' => 'Type them in, paste a flyer and let the AI read it, or turn on the booking inbox and let the acts submit their own dates for you to approve.'],
            ['name' => 'Share it and sell', 'text' => 'Put the link in your bio, embed the calendar on your own website, and turn on ticketing whenever you are ready to sell.'],
        ];

        $faqs = [
            [
                'q' => 'Can I manage multiple rooms or stages?',
                'a' => 'Yes, as sub-schedules. Main stage, rooftop bar, private room - give each one a name and a colour, and it gets its own filterable view and its own page within your calendar. Visitors can filter by space or see everything at once. A sub-schedule labels an event rather than reserving a space, so it will not warn you if two things land in the same room.',
            ],
            [
                'q' => 'How do performers request to play at my venue?',
                'a' => 'Enable the booking inbox on your schedule, and musicians, DJs, and other performers can submit requests to play at your venue. You review each request and approve or decline from your dashboard. Approved events are automatically added to your calendar.',
            ],
            [
                'q' => 'Can I embed the calendar on my venue\'s website?',
                'a' => 'Yes. Copy a simple embed code and paste it into your website. The calendar updates automatically whenever you add or change events. It works with any website builder including WordPress, Squarespace, and Wix.',
            ],
            [
                'q' => 'Can multiple staff members manage the calendar?',
                'a' => 'Your schedule includes one team member on the free plan, and the Enterprise plan adds up to five. Each member is either an admin, who can add and edit events, or a viewer, who can see the schedule without changing it. Anyone with access to the schedule can scan tickets at the door from their own phone.',
            ],
            [
                'q' => 'Can people book our function room or studio time?',
                'a' => 'Yes. Create a bookable type for the space you rent out, set the hours you are available, add per-date overrides for holidays, and charge for it through Stripe if you want to. The free plan carries one bookable type and Pro removes the cap. Guests pick a time on your public booking page, and you can require approval before anything is confirmed.',
            ],
            [
                'q' => 'Can we use our own domain and remove Event Schedule branding?',
                'a' => 'Removing the Event Schedule branding is part of the Pro plan, along with custom CSS, sponsor logos, an announcement banner and your own favicon. Serving the schedule from your own address, like events.yourvenue.com, is an Enterprise feature and the SSL certificate is issued automatically.',
            ],
            [
                'q' => 'What does it cost to sell tickets?',
                'a' => 'Selling is included on every plan, with the Free plan capped at 25 paid tickets a month and Pro removing the cap. Event Schedule charges no platform fee on ticket sales at any tier, so the only deduction is Stripe\'s standard processing fee, and payouts go straight to your own Stripe account.',
            ],
        ];

        $dotSections = [
            ['top', 'Top'],
            ['week', 'Your week'],
            ['front-of-house', 'Front of house'],
            ['back-of-house', 'Back of house'],
            ['venues', 'Every kind of venue'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        $jumpChips = [
            ['#week', 'Your week'],
            ['#front-of-house', 'Front of house'],
            ['#back-of-house', 'Back of house'],
            ['#venues', 'Venues'],
            ['#faq', 'FAQ'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- Hero                                                        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(78svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 34%, rgba(56, 189, 248, 0.42), rgba(56, 189, 248, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 40%, rgba(34, 211, 238, 0.34), rgba(34, 211, 238, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 60%, rgba(14, 165, 233, 0.16), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
            <div class="es-foh-light es-foh-light-1"></div>
            <div class="es-foh-light es-foh-light-2"></div>
            <div class="es-foh-light es-foh-light-3"></div>
        </div>

        {{-- No facade drawing behind the headline: removed by request. The
             elevation still appears at the pass door and the finale, where it
             is not competing with the H1 and the CTAs. --}}

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-7 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-cyan-400" aria-hidden="true"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Event calendar for bars, clubs, theaters and event spaces</span>
            </div>

            <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Fill the room.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-house es-gradient-anim">Run the show.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-9 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                One calendar your audience can follow, and everything your staff needs behind it. Free forever, with zero platform fees on ticket sales.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Create your calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#front-of-house" class="group inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>

            {{-- Jump row: the mobile wayfinding answer, since the dot nav is lg-only. --}}
            <nav class="es-fade-up es-d-4 mt-9 flex flex-wrap items-center justify-center gap-2 lg:hidden" aria-label="Jump to a section">
                @foreach ($jumpChips as [$chipHref, $chipLabel])
                    <a href="{{ $chipHref }}" class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3.5 py-1.5 text-sm font-medium text-sky-700 transition-colors hover:bg-sky-100 dark:border-sky-400/30 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:bg-sky-500/20">{{ $chipLabel }}</a>
                @endforeach
            </nav>

            {{-- Venue-type ticker. Decorative: all twelve are linked properly in the
                 directory further down, so 24 duplicate anchors would be noise. --}}
            <div class="es-fade-up es-d-5 mx-auto mt-10 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($typeCopy = 0; $typeCopy < 2; $typeCopy++)
                                @foreach (['Bars', 'Nightclubs', 'Music Venues', 'Theaters', 'Comedy Clubs', 'Restaurants', 'Breweries', 'Art Galleries', 'Community Centers', 'Hotels', 'Libraries', 'Farmers Markets'] as $type)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-cyan-400"></span>
                                        {{ $type }}
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
    <!-- Your week: the shape of the thing, before any feature talk   -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 border-y border-gray-200 bg-gray-50 py-14 dark:border-white/10 dark:bg-[#0f0f14] lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-2xl" data-reveal>
                <h2 class="es-balance mb-2 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl">
                    A week at your place, on <span class="text-gradient-house">one page</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400">Weekly regulars, one-off shows, ticketed nights and the room you rent out. All on the same calendar, all public the moment you save them.</p>
            </div>

            {{-- Seven cells, so the grid is complete stacked on a phone and as a
                 week on a desktop. No intermediate column count would divide 7. --}}
            <div class="grid grid-cols-1 gap-2.5 md:grid-cols-7" data-reveal-group="60">
                @foreach ($week as $slot)
                    @if (empty($slot['name']))
                        <div data-reveal class="flex items-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-transparent p-3.5 dark:border-white/15 md:min-h-[7.5rem] md:flex-col md:items-stretch md:gap-2">
                            <div class="w-10 shrink-0 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 md:w-auto">{{ $slot['day'] }}</div>
                            <div class="flex items-center gap-1.5 text-sm text-gray-400 dark:text-gray-500 md:mt-auto">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
                                Free night
                            </div>
                        </div>
                    @else
                        <div data-reveal class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-3.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-sky-500/30 md:min-h-[7.5rem] md:flex-col md:items-stretch md:gap-2">
                            <div class="w-10 shrink-0 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 md:w-auto">{{ $slot['day'] }}</div>
                            <div class="min-w-0 flex-1 md:flex-none">
                                <div class="truncate text-sm font-semibold text-gray-900 dark:text-white md:whitespace-normal">{{ $slot['name'] }}</div>
                            </div>
                            <span class="ms-auto inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[11px] font-medium md:ms-0 md:mt-auto md:self-start {{ $slot['tone'] === 'loud' ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">{{ $slot['tag'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- ACT 01: Front of house                                       -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="01"
        id="front-of-house"
        label="Act"
        accent="sky"
        title="Front of house"
        lede="What your audience sees. The calendar they follow, the tickets they buy, the emails that bring them back, and the room they can book for themselves."
        ground="white" />

    <x-marketing.feature-banner
        :href="marketing_url('/features/embed-calendar')"
        accent="sky"
        badge="Public calendar"
        heading="A calendar your regulars actually follow"
        lede="One page with everything you have on. Share the link, embed it on your own website, or let people subscribe so your nights land in the calendar app they already use."
        :chips="['Mobile-friendly', 'One link', 'Follow button', 'iCal and RSS feeds', 'Embed anywhere']"
        :lead="true"
        frame="browser"
        frame-url="thebluenote.eventschedule.com"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>

        <div class="mb-4 flex items-center justify-between">
            <div>
                <div class="text-base font-bold text-gray-900 dark:text-white">The Blue Note</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Upcoming events</div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Follow
            </span>
        </div>
        <div class="space-y-2">
            @foreach ([['Thu 12', 'Open Mic', '8pm'], ['Fri 13', 'The Rialtos', '8pm'], ['Sat 14', 'Soul Night', '9pm'], ['Sun 15', 'Makers Market', '11am']] as $i => [$when, $what, $startTime])
                <div class="es-ai-field flex items-center gap-3 rounded-lg border p-2.5 {{ $i === 1 ? 'border-sky-400/40 bg-sky-500/10' : 'border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5' }}" style="--i: {{ $i }};">
                    <div class="w-12 shrink-0 font-mono text-[11px] text-sky-600 dark:text-sky-300">{{ $when }}</div>
                    <span class="min-w-0 flex-1 truncate text-sm text-gray-900 dark:text-white">{{ $what }}</span>
                    <span class="shrink-0 text-[11px] text-gray-500 dark:text-gray-400">{{ $startTime }}</span>
                </div>
            @endforeach
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/features/ticketing')"
        accent="cyan"
        badge="Box office"
        heading="Sell tickets without giving away the door"
        lede="Set your ticket types, connect your own Stripe account and keep every cent of the face value. Event Schedule takes no platform fee, so the only deduction is Stripe's processing charge."
        :chips="['Zero platform fees', 'Stripe payouts', 'Promo codes', 'Gift cards', 'Waitlists', 'Free RSVPs']"
        :flip="true"
        frame="phone"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
        </x-slot>

        <div class="mb-3 text-center">
            <div class="text-sm font-bold text-gray-900 dark:text-white">The Rialtos</div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">Fri 13, 8pm</div>
        </div>
        <div class="mb-3 flex justify-center">
            <div class="relative rounded-xl bg-white p-2 shadow-md ring-1 ring-gray-200 dark:ring-white/10">
                <svg class="h-20 w-20 text-gray-900" viewBox="0 0 29 29" fill="currentColor" aria-hidden="true">
                    <path d="M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z"/>
                </svg>
                <div class="es-laser"></div>
            </div>
        </div>
        <div class="es-checkin mx-auto w-fit rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Checked in</div>
        <div class="mt-3 space-y-1.5">
            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 text-[11px] dark:bg-white/5">
                <span class="text-gray-600 dark:text-gray-300">General admission</span>
                <span class="font-semibold text-gray-900 dark:text-white">$12</span>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 text-[11px] dark:bg-white/5">
                <span class="text-gray-600 dark:text-gray-300">Platform fee</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ plan_price(0) }}</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/features/custom-domain')"
        accent="blue"
        badge="Your brand"
        heading="Your name on the door, not ours"
        lede="Put the calendar on your own address, drop our branding entirely, and dress the page to match the room. Regulars should not be able to tell where your site ends and the calendar begins."
        :chips="['Custom domain', 'No Event Schedule branding', 'Custom CSS', 'Sponsor logos', 'Your favicon', 'Announcement banner']"
        frame="browser"
        frame-url="events.thebluenote.com"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
            </svg>
        </x-slot>

        <div class="mb-4 flex items-center gap-3 border-b border-gray-200 pb-3 dark:border-white/10">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-cyan-500 text-sm font-black text-white">BN</span>
            <div class="min-w-0">
                <div class="truncate text-sm font-bold text-gray-900 dark:text-white">The Blue Note</div>
                <div class="truncate text-[11px] text-gray-500 dark:text-gray-400">What's on</div>
            </div>
        </div>
        <div class="space-y-2">
            <div class="h-2.5 w-4/5 rounded-full bg-gray-200 dark:bg-white/10"></div>
            <div class="h-2.5 w-3/5 rounded-full bg-gray-200 dark:bg-white/10"></div>
        </div>
        <div class="mt-4 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-center text-[11px] text-gray-400 dark:border-white/15 dark:text-gray-500">
            No "powered by" line here
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/features/appointments')"
        accent="teal"
        badge="Private hire"
        heading="Let people book the room"
        lede="The function room, the studio, the back bar, a venue tour. Publish the hours each space is free and let people pick a time themselves, with the price paid up front if you want it."
        :chips="['Bookable spaces', 'Weekly hours', 'Holiday overrides', 'Paid by Stripe', 'Approval required', 'Reschedule links']"
        :flip="true"
        frame="panel"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-slot>

        <div class="mb-3 flex items-center justify-between">
            <div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">Back room hire</div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">2 hours, up to 40 people</div>
            </div>
            <span class="rounded-full bg-teal-100 px-2.5 py-1 text-[11px] font-semibold text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">$60 to book</span>
        </div>
        <div class="mb-3 text-[11px] font-medium text-gray-500 dark:text-gray-400">Saturday 21</div>
        <div class="grid grid-cols-3 gap-2">
            @foreach (['11:00', '13:00', '15:00', '17:00', '19:00', '21:00'] as $i => $timeSlot)
                <div class="es-ai-field rounded-lg border py-2 text-center text-[11px] font-medium {{ $i === 3 ? 'border-teal-400/50 bg-teal-500/15 text-teal-700 dark:text-teal-300' : 'border-gray-200 bg-gray-50 text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300' }}" style="--i: {{ $i }};">{{ $timeSlot }}</div>
            @endforeach
        </div>
        <div class="mt-3 flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-[11px] text-gray-500 dark:bg-white/5 dark:text-gray-400">
            <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Needs your approval before it is confirmed
        </div>
    </x-marketing.feature-banner>

    <!-- ------------------------------------------------------------ -->
    <!-- Also out front                                               -->
    <!-- ------------------------------------------------------------ -->
    @php
        $alsoOutFront = [
            [
                'title' => 'Followers and newsletters',
                'desc' => 'People follow your page, you email them when new dates land.',
                'href' => marketing_url('/features/newsletters'),
                'chip' => 'bg-sky-100 dark:bg-sky-500/15', 'text' => 'text-sky-600 dark:text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
            ],
            [
                'title' => 'Event graphics',
                'desc' => 'A shareable image and ready-written caption for every show.',
                'href' => marketing_url('/features/event-graphics'),
                'chip' => 'bg-cyan-100 dark:bg-cyan-500/15', 'text' => 'text-cyan-600 dark:text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            ],
            [
                'title' => 'Boost ads',
                'desc' => 'Turn a show into a Facebook and Instagram campaign without leaving the page.',
                'href' => marketing_url('/features/boost'),
                'chip' => 'bg-orange-100 dark:bg-orange-500/15', 'text' => 'text-orange-600 dark:text-orange-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
            ],
            [
                'title' => 'Online events',
                'desc' => 'Paste the join link and sell to people who cannot get to the room.',
                'href' => marketing_url('/features/online-events'),
                'chip' => 'bg-blue-100 dark:bg-blue-500/15', 'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />',
            ],
            [
                'title' => 'Fan videos and polls',
                'desc' => 'Clips and comments after the show, votes on what to book next.',
                'href' => marketing_url('/features/fan-videos'),
                'chip' => 'bg-teal-100 dark:bg-teal-500/15', 'text' => 'text-teal-600 dark:text-teal-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />',
            ],
            [
                'title' => 'Gift cards',
                'desc' => 'Sell a balance someone can spend on any night you put on.',
                'href' => marketing_url('/features/gift-cards'),
                'chip' => 'bg-emerald-100 dark:bg-emerald-500/15', 'text' => 'text-emerald-600 dark:text-emerald-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />',
            ],
        ];
    @endphp
    <section class="bg-white py-12 dark:bg-[#0a0a0f] lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-balance mb-8 text-xl font-black tracking-tight text-gray-900 dark:text-white md:text-2xl" data-reveal>Also out front</h2>
            {{-- Six cards at two and three columns: complete rows at every
                 breakpoint, and two columns even at 390px so the section does not
                 turn into six stacked paragraphs. --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3" data-reveal-group="55">
                @foreach ($alsoOutFront as $item)
                    <a href="{{ $item['href'] }}" data-reveal class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-3.5 transition-all duration-200 hover:-translate-y-1 hover:border-sky-300 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-sky-500/40 sm:p-5">
                        <div class="mb-2.5 flex items-center gap-2.5 sm:gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['chip'] }} sm:h-9 sm:w-9">
                                <svg class="h-4 w-4 {{ $item['text'] }} sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            </span>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">{{ $item['title'] }}</h3>
                        </div>
                        <p class="hidden flex-grow text-sm text-gray-600 dark:text-gray-400 sm:block">{{ $item['desc'] }}</p>
                        <span class="mt-auto hidden items-center gap-1 pt-3 text-sm font-medium {{ $item['text'] }} transition-all group-hover:gap-2 sm:inline-flex">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center" data-reveal>
                <a href="{{ marketing_url('/features') }}" class="group inline-flex items-center gap-1.5 font-medium text-sky-600 hover:underline dark:text-sky-400">
                    See all features
                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The pass door: the hinge, and the mid-page CTA               -->
    <!-- ============================================================ -->
    <section id="pass" class="es-band-dark noise relative scroll-mt-24 overflow-hidden px-4 py-16 sm:px-6 lg:py-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-foh-fade absolute inset-x-0 top-0 h-28"></div>
            <div class="grid-overlay absolute inset-0 opacity-20"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-3xl text-center" data-reveal>
            <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5">
                <svg aria-hidden="true" class="h-3.5 w-3.5 text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4V4H4m0 16h16M14 12h.01M10 4h10v16" /></svg>
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">Load in</span>
            </div>
            <h2 class="es-balance mb-4 text-2xl font-black tracking-tight text-white md:text-4xl">
                Front of house is what they see. <span class="text-gradient-house">Back of house is what you run.</span>
            </h2>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-300">
                Both sides come with the schedule. Start with the calendar, turn the rest on when you need it.
            </p>
            <a href="{{ app_url('/sign_up?type=venue') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-300">
                Create your calendar
                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- ACT 02: Back of house (fixed dark in both colour modes)      -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="02"
        id="back-of-house"
        label="Act"
        accent="cyan"
        title="Back of house"
        lede="What your staff runs. The inbox the acts write into, the door on the night, the rooms, the sync, and the numbers on Monday morning."
        ground="dark" />

    <x-marketing.feature-banner
        :href="marketing_url('/docs/managing-schedules') . '#requests'"
        accent="emerald"
        badge="Booking inbox"
        heading="Let the acts come to you"
        lede="Turn on the public request form and performers submit their own dates. Ask whatever you need to know up front, approve or decline in one click, and let the acts you trust skip the queue entirely."
        :chips="['Public request form', 'Custom questions', 'Approve or decline', 'Auto-approve trusted acts', 'Email notifications']"
        :lead="true"
        frame="panel"
        ground="dark">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </x-slot>

        <div class="mb-3 flex items-center justify-between">
            <div class="text-sm font-bold text-gray-900 dark:text-white">Requests</div>
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">3 waiting</span>
        </div>
        <div class="relative rounded-xl border border-gray-200 bg-gray-50 p-3.5 dark:border-white/10 dark:bg-white/5">
            <div class="mb-2 flex items-center gap-2.5">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 text-[11px] font-bold text-white">TR</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">The Rialtos</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Fri 13, 8pm, four-piece</div>
                </div>
            </div>
            <div class="space-y-1 text-[11px] text-gray-500 dark:text-gray-400">
                <div><span class="font-medium text-gray-700 dark:text-gray-300">Backline needed:</span> Drums and bass amp</div>
                <div><span class="font-medium text-gray-700 dark:text-gray-300">Fee:</span> Door split</div>
            </div>
            <div class="es-foh-stamp absolute rounded-md px-2.5 py-1 text-[11px] font-black uppercase" style="right: 10px; bottom: 10px;">Accepted</div>
        </div>
        <div class="mt-2.5 space-y-2">
            @foreach ([['Marla Vance', 'Sat 21, solo set'], ['Hexbridge', 'Thu 26, support slot']] as $i => [$actName, $actNote])
                <div class="es-ai-field flex items-center gap-2.5 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5" style="--i: {{ $i + 1 }};">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                    <span class="min-w-0 flex-1 truncate text-[11px] text-gray-700 dark:text-gray-300">{{ $actName }}</span>
                    <span class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500">{{ $actNote }}</span>
                </div>
            @endforeach
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/docs/tickets') . '#check-in'"
        accent="sky"
        badge="The door"
        heading="Scan them in with the phone in your pocket"
        lede="Open the scanner, point it at the QR code, done. The check-in dashboard shows who is inside and how many are still to come, updating live as the queue moves."
        :chips="['QR check-in', 'Live check-in dashboard', 'Individual tickets', 'Bulk attendee import', 'No extra hardware']"
        :flip="true"
        frame="phone"
        ground="dark">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2M8 12h8" />
            </svg>
        </x-slot>

        <div class="mb-3 text-center text-[11px] font-medium text-gray-500 dark:text-gray-400">Soul Night, Sat 14</div>
        <div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 p-3 text-center dark:border-white/10 dark:bg-white/5">
            <div class="text-2xl font-black text-gray-900 dark:text-white">128<span class="text-base font-bold text-gray-400 dark:text-gray-500"> / 240</span></div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">Checked in</div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                <div class="es-bar h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-500" style="width: 53%; --bd: 0.1s;"></div>
            </div>
        </div>
        <div class="space-y-1.5">
            @foreach ([['General admission', '96 / 180'], ['Early bird', '24 / 40'], ['Comps', '8 / 20']] as $i => [$tierName, $tierCount])
                <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 text-[11px] dark:bg-white/5" style="--i: {{ $i }};">
                    <span class="text-gray-600 dark:text-gray-300">{{ $tierName }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $tierCount }}</span>
                </div>
            @endforeach
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/features/sub-schedules')"
        accent="cyan"
        badge="Rooms and stages"
        heading="Main stage, rooftop, back room"
        lede="Give every space its own sub-schedule, with its own name and colour. Visitors can filter down to the room they care about, open the page for that room alone, or see the whole building at once."
        :chips="['Sub-schedules', 'Filterable views', 'A page per room', 'Recurring nights per space']"
        frame="panel"
        ground="dark">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </x-slot>

        <div class="mb-3 flex items-center gap-2">
            <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Rooms</span>
            <span class="ms-auto text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Filter by room</span>
        </div>
        <div class="space-y-1.5">
            @foreach ([['Main Stage', '12 events', 'bg-sky-400', true], ['Rooftop Bar', '8 events', 'bg-cyan-400', false], ['Back Room', '5 events', 'bg-blue-400', false], ['Patio', '3 events', 'bg-teal-400', false]] as $i => [$roomName, $roomCount, $roomDot, $roomActive])
                <div class="es-ai-field flex items-center gap-2 rounded-lg p-2 {{ $roomActive ? 'border border-sky-400/30 bg-sky-500/15' : 'bg-gray-100 dark:bg-white/5' }}" style="--i: {{ $i }};">
                    <div class="h-2 w-2 shrink-0 rounded-full {{ $roomDot }}"></div>
                    <span class="min-w-0 flex-1 truncate text-[13px] {{ $roomActive ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">{{ $roomName }}</span>
                    <span class="shrink-0 text-[11px] {{ $roomActive ? 'text-sky-700 dark:text-sky-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $roomCount }}</span>
                </div>
            @endforeach
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/features/analytics')"
        accent="teal"
        badge="The numbers"
        heading="What sold, what did not, and where they came from"
        lede="See which nights pull, which channels send people, and what your room earned. Export the sales with every custom field, and collect star ratings from the people who actually turned up."
        :chips="['Page views', 'Traffic sources', 'Appearance views', 'Sales CSV export', 'Post-event feedback']"
        :flip="true"
        frame="browser"
        frame-url="thebluenote.eventschedule.com/analytics"
        ground="dark">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </x-slot>

        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">Platform fees, all time</div>
                <div class="es-od text-gradient-house text-4xl font-black" data-odometer="{{ plan_price(0) }}">{{ plan_price(0) }}</div>
            </div>
            <div class="text-end">
                <div class="text-[11px] text-gray-500 dark:text-gray-400">Tickets sold</div>
                <div class="text-2xl font-black text-gray-900 dark:text-white">1,462</div>
            </div>
        </div>
        <div class="mb-4 flex h-24 items-end gap-1.5" aria-hidden="true">
            @foreach ([38, 56, 34, 72, 90, 64, 48, 82, 96, 58, 44, 76] as $i => $barHeight)
                <div class="flex-1 overflow-hidden rounded-t bg-gray-100 dark:bg-white/5" style="height: 100%;">
                    <div class="es-bar h-full w-full origin-bottom rounded-t bg-gradient-to-t from-sky-500 to-cyan-400" style="height: {{ $barHeight }}%; margin-top: {{ 100 - $barHeight }}%; --bd: {{ 0.04 * $i }}s;"></div>
                </div>
            @endforeach
        </div>
        <div class="space-y-1.5">
            @foreach ([['Direct link', '41%'], ['Instagram', '27%'], ['Newsletter', '19%']] as $i => [$sourceName, $sourcePct])
                <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 text-[11px] dark:bg-white/5" style="--i: {{ $i }};">
                    <span class="text-gray-600 dark:text-gray-300">{{ $sourceName }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $sourcePct }}</span>
                </div>
            @endforeach
        </div>
    </x-marketing.feature-banner>

    <!-- ------------------------------------------------------------ -->
    <!-- And behind the scenes (still on the dark ground)             -->
    <!-- ------------------------------------------------------------ -->
    @php
        $behindTheScenes = [
            [
                'title' => 'Two-way calendar sync',
                'desc' => 'Google, Outlook and CalDAV. Change it anywhere, it changes everywhere.',
                'href' => marketing_url('/features/calendar-sync'),
                'text' => 'text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
            ],
            [
                'title' => 'Who has played here',
                'desc' => 'A wall of logos built automatically from the acts you accepted.',
                'href' => marketing_url('/docs/schedule-styling') . '#header-images',
                'text' => 'text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />',
            ],
            [
                'title' => 'Your team',
                'desc' => 'Admins who can edit, viewers who can only look, one login each.',
                'href' => marketing_url('/features/team-scheduling'),
                'text' => 'text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />',
            ],
            [
                'title' => 'Recurring nights',
                'desc' => 'Set the quiz for every Tuesday once, with exceptions for the weeks you skip.',
                'href' => marketing_url('/features/recurring-events'),
                'text' => 'text-teal-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            ],
        ];
    @endphp
    <section class="es-band-dark noise relative overflow-hidden py-12 lg:py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-foh-worklight"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-balance mb-8 text-xl font-black tracking-tight text-white md:text-2xl" data-reveal>And behind the scenes</h2>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" data-reveal-group="55">
                @foreach ($behindTheScenes as $item)
                    <a href="{{ $item['href'] }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-3.5 transition-all duration-200 hover:-translate-y-1 hover:border-sky-400/40 hover:bg-white/[0.07] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-300 sm:p-5">
                        <div class="mb-2.5 flex items-center gap-2.5 sm:gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.08] sm:h-9 sm:w-9">
                                <svg class="h-4 w-4 {{ $item['text'] }} sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            </span>
                            <h3 class="text-sm font-semibold text-white sm:text-base">{{ $item['title'] }}</h3>
                        </div>
                        <p class="hidden flex-grow text-sm text-gray-400 sm:block">{{ $item['desc'] }}</p>
                        <span class="mt-auto hidden items-center gap-1 pt-3 text-sm font-medium {{ $item['text'] }} transition-all group-hover:gap-2 sm:inline-flex">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ------------------------------------------------------------ -->
    <!-- Three steps, closing the dark act                            -->
    <!-- ------------------------------------------------------------ -->
    <x-seo.howto-schema
        name="Get your venue calendar online"
        description="Create a schedule for your venue, add your events, and share the calendar with your audience."
        :steps="$howToSteps" />
    <section id="steps" class="es-band-dark noise relative scroll-mt-24 overflow-hidden pb-16 pt-4 lg:pb-24 lg:pt-6">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="grid-overlay absolute inset-0 opacity-20"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center" data-reveal>
                <h2 class="es-balance text-2xl font-black tracking-tight text-white md:text-4xl">Open the doors in <span class="text-gradient-house">three steps</span></h2>
            </div>
            <ol class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ($howToSteps as $i => $step)
                    <li data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 text-base font-black text-white">{{ $i + 1 }}</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">{{ $step['name'] }}</h3>
                        <p class="text-sm text-gray-400">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Every kind of venue                                          -->
    <!-- ============================================================ -->
    <section id="venues" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl" data-reveal>
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 dark:bg-cyan-500/20">
                        <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Every kind of <span class="text-gradient-house">venue</span></h2>
                </div>
                <p class="text-lg text-gray-600 dark:text-gray-400">From a back-room stage to a season of productions, a Saturday market to a library reading room. The same calendar, the same booking inbox, the same box office.</p>

                <a href="{{ marketing_url('/use-cases') }}"
                   class="group mt-5 inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-200 transition-all hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-cyan-300 dark:ring-cyan-400/30 dark:hover:bg-cyan-500/10">
                    See every use case
                    <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">
                @foreach ($venueTypes as $v)
                    <x-marketing.audience-card
                        :url="marketing_url($v['url'])"
                        :name="$v['name']"
                        :blurb="$v['blurb']"
                        accent="cyan"
                        :tags="$v['tags']">
                        <x-slot name="icon">{!! $v['icon'] !!}</x-slot>
                    </x-marketing.audience-card>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The stack this replaces                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-14 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center" data-reveal>
                <h2 class="es-balance mb-3 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                    Most rooms run this on <span class="text-gradient-house">seven tabs</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400">A form, a spreadsheet, a mailing list, a design tool, a bio link, a booking link and a QR generator. One schedule does all of it.</p>
            </div>

            {{-- One row per tool rather than two facing columns. The two-column
                 version stacked into fourteen rows on a phone and made the reader
                 hold the left list in their head while scrolling the right one. --}}
            <div data-reveal="panel" class="overflow-hidden rounded-3xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]">
                <div class="hidden border-b border-gray-200 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:text-gray-500 sm:grid sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:gap-4">
                    <span>What you run today</span>
                    <span class="w-4"></span>
                    <span>With one schedule</span>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($replaces as $r)
                        {{-- The arrow is a desktop-only affordance: on a phone the two
                             lines already read top to bottom, and a third row per item
                             cost ~200px of scroll for punctuation. --}}
                        <li class="grid gap-0.5 px-5 py-3 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:gap-4">
                            <a href="{{ marketing_url($r['url']) }}" class="text-sm text-gray-500 underline-offset-4 transition-colors hover:text-sky-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-gray-400 dark:hover:text-sky-400">{{ $r['tool'] }}</a>
                            <svg aria-hidden="true" class="hidden h-4 w-4 shrink-0 text-sky-500 sm:block rtl:sm:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $r['now'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>
                Moving off a ticketing platform? See how we compare to
                <a href="{{ marketing_url('/eventbrite-alternative') }}" class="font-medium text-sky-600 hover:underline dark:text-sky-400">Eventbrite</a>
                and <a href="{{ marketing_url('/dice-alternative') }}" class="font-medium text-sky-600 hover:underline dark:text-sky-400">DICE</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Plans, for a venue                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-14 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 max-w-2xl text-center" data-reveal>
                <h2 class="es-balance mb-3 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                    Free forever. <span class="text-gradient-house">Upgrade when the room does.</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400">Zero platform fees on ticket sales at every tier. You only ever pay Stripe's processing fee.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ($plans as $plan)
                    <div data-reveal="panel" class="flex flex-col rounded-3xl border p-5 lg:p-7 {{ $plan['featured'] ? 'border-sky-300 bg-gradient-to-br from-sky-50 to-cyan-50 shadow-lg shadow-sky-500/10 dark:border-sky-500/30 dark:from-sky-950/40 dark:to-cyan-950/40' : 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]' }}">
                        <div class="mb-1 flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                            @if ($plan['featured'])
                                <span class="rounded-full bg-sky-600 px-2 py-0.5 text-[11px] font-semibold text-white">Most venues</span>
                            @endif
                        </div>
                        <div class="mb-2.5 flex items-baseline gap-1.5">
                            <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $plan['note'] }}</span>
                        </div>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">{{ $plan['lede'] }}</p>
                        <ul class="mb-5 space-y-1.5">
                            @foreach ($plan['items'] as $item)
                                <li class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                                    <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ marketing_url('/pricing') }}" class="group mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-sky-600 transition-all hover:gap-2.5 dark:text-sky-400">
                            Compare the plans
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                          -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />
    <section id="faq" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-balance mb-10 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                Everything venues ask
            </h2>
            <div class="space-y-3" data-reveal-group="60">
                @foreach ($faqs as $faq)
                    <details name="faq" class="group rounded-2xl border border-gray-200 bg-white px-5 py-4 transition-colors hover:border-sky-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-sky-500/40" data-reveal>
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ $faq['q'] }}
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="mt-3 text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- Finale: the lights come back up out front                     -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-foh-light es-foh-light-1"></div>
                    <div class="es-foh-light es-foh-light-2"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-house">fill the room?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Claim your venue's address and put your first night up in minutes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
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

    <!-- Desktop dot nav -->
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
    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
