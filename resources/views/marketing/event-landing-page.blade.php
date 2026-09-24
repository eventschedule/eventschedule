<x-marketing-layout>
    <x-slot name="title">Event Landing Page, Free | Tickets, RSVP and a Map Built In</x-slot>
    <x-slot name="description">Every event gets a free landing page: the flyer, the date and time, the venue with a map, tickets or free RSVP, add to calendar and sharing, all built in.</x-slot>
    <x-slot name="breadcrumbTitle">Event Landing Page</x-slot>

    {{-- What an event page carries, read off event/show-guest.blade.php and the layout rather than
         off other marketing pages:
         - The flyer (with a full-size lightbox), the description, the lineup linked to each act's
           page, and the venue with a map and a Google Maps link. An online event's join link is
           NOT on the page: it goes on the ticket (see /features/online-events).
         - The date and time are the schedule's own (Event::getStartDateTime() in
           scheduleTimezone()), with a machine-readable <time datetime>.
         - Buy or Register, a waitlist on a full date, the "Notify me" card where the schedule has
           switched it on, Add to Calendar (Google, Apple, Microsoft Outlook) and Share. On a phone
           the button bar is pinned to the bottom of the screen (#gp-mobile-cta).
         - Link previews use Event::shareImage(): the flyer, then a performer's, the venue's or the
           schedule's own upload, and nothing at all rather than an image of ours
           (GuestSocialImageTest). The title is GuestSeo::eventTitle(): name, date and venue.
         Tiers, from docs/FEATURES.md: a page and free registration on every plan; a ticket with a
         price, custom CSS and removing our branding on Pro; a custom domain on Enterprise.

         Differentiation: /squarespace-replacement is the head-to-head with a website builder;
         /features/ticketing and /features/registration own the buy and register halves. This page
         is the anatomy of the page itself, and links to all three. --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Event Landing Page"
        description="A free landing page for every event: the flyer, the date and time, the venue with a map, tickets or free registration, add to calendar, sharing and event structured data for search."
        keywords="event landing page, event page, free event page, event website, event page with tickets, event registration page" />
    </x-slot>

    @php
        $anatomy = [
            ['The flyer', 'Upload the poster and it leads the page, with a tap to see it full size. It is also the picture a link preview shows.', 'Free'],
            ['What it is', 'Your description, formatted the way you wrote it, with headings, lists and links, and the whole lineup, each act linked to its own page where it has one.', 'Free'],
            ['When', 'The date and the start time as your schedule keeps them, in its own time zone, so a visitor abroad still sees when the doors open where the event is.', 'Free'],
            ['Where', 'The venue with its address and a map, and a link that opens the address in Google Maps. An online event says Online, and its join link goes on the ticket instead.', 'Free'],
            ['Register or buy', 'A Register button for a free event, with a cap per date and a waitlist when it fills. A Buy button for ticket types with a price, on Pro, with zero platform fees.', 'Free / Pro'],
            ['Add to calendar', 'Google Calendar, Apple Calendar and Microsoft Outlook, one tap each, so the date ends up where the guest will actually see it.', 'Free'],
            ['Tell me when', 'Before tickets go on sale, a visitor can leave an email address to hear when they do, once you switch the card on for your schedule.', 'Free'],
            ['Share', 'A share button that uses the phone\'s own share sheet, or copies the link. On a phone the ticket and share buttons stay pinned to the bottom of the screen.', 'Free'],
        ];

        $landingFaqs = [
            ['q' => 'What is an event landing page?', 'a' => 'A page for one event, built to turn a visitor into a guest: what it is, when and where, and a button to register or buy a ticket. On Event Schedule every event you publish gets one automatically, with its own link, and nothing to design.'],
            ['q' => 'Do I need a website?', 'a' => 'No. Each event page has its own link on your schedule, which you can share anywhere. If you already have a website, embed your whole calendar on it, free, and each event still opens its own page.'],
            ['q' => 'Can people buy tickets on the page?', 'a' => 'Yes. Free registration works on every plan, with a cap per date and a waitlist. Putting a price on a ticket is the Pro plan, paid through your own Stripe or PayPal account, and there is no platform fee on any plan.'],
            ['q' => 'What shows up when I share the link?', 'a' => 'The event name, a description from your own text, and your flyer. With no flyer the preview uses a performer\'s, the venue\'s or your schedule\'s own picture, and with none of those it shows no picture at all, rather than an advert of ours.'],
            ['q' => 'Will the page show up on Google?', 'a' => 'It is built to be found. The page title carries the date and the venue, the description comes from your own text, and every event page carries event structured data describing what the page shows. Once your schedule\'s email address is confirmed, search engines are invited to index its pages and its events are listed in the sitemap. A recurring event keeps one page for the whole series rather than one per week. Nobody can promise a ranking, but nothing on the page stands in the way.'],
            ['q' => 'Can I use my own domain or remove your branding?', 'a' => 'Yes. Removing Event Schedule branding is on Pro, and custom CSS for the finer details is too. A custom domain, so the page lives at your own address, is on Enterprise. Colours, fonts, backgrounds and header images are free on every plan.'],
            ['q' => 'How is this different from building a page on a website builder?', 'a' => 'A website builder is a blank page for anything, and an event page on one is yours to assemble. Here the page is built around the event: the date, the map, the ticket button and the calendar buttons are already in place, filled in from the event you entered.', 'link' => [marketing_url('/squarespace-replacement'), 'Compare with Squarespace']],
        ];
    @endphp

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Event landing page: "The Page".

           CONCEPT: the page itself. The hero is a picture of an event page
           the way a guest meets it on a phone, and the page below walks
           through it part by part, then says who finds it and how it
           becomes yours. No drawing: the concept is carried by the page
           card, which is FIXED white in both modes because it is a picture
           of a page the guest may see in either theme.

           DELIBERATELY NOT: /squarespace-replacement owns the comparison
           with a website builder; /features/ticketing and
           /features/registration own the two buttons. This page is the
           anatomy.

           COLOUR: the site's blue family, sky to cyan.
             light ground #ffffff: accent #0369a1 5.9
             dark ground  #0a0a0f: accent #7dd3fc 13.5
           ============================================================== */

        .es-page-accent { color: #0369a1; }
        .dark .es-page-accent { color: #7dd3fc; }

        .es-page-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #0369a1;
        }
        .dark .es-page-tag { color: #7dd3fc; }

        .text-gradient-page {
            background: linear-gradient(135deg, #1d4ed8 0%, #0369a1 55%, #0e7490 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-page,
        .es-finale-panel .text-gradient-page {
            background: linear-gradient(135deg, #60a5fa 0%, #7dd3fc 55%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .es-page-pill {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            border-radius: 9999px;
            padding: 0.125rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background-color: #e0f2fe;
            color: #075985;
        }
        .dark .es-page-pill { background-color: rgba(125, 211, 252, 0.14); color: #7dd3fc; }

        .es-page-btn { background-color: #0369a1; color: #ffffff; }
        .es-page-btn:hover { background-color: #075985; }
        .es-page-ghost { border-color: #d1d5db; color: #111827; }
        .es-page-ghost:hover { border-color: #0369a1; }
        .dark .es-page-ghost { border-color: rgba(255, 255, 255, 0.15); color: #ffffff; }
        .dark .es-page-ghost:hover { border-color: #7dd3fc; }

        .es-page-num { background-color: #e0f2fe; color: #075985; }
        .dark .es-page-num { background-color: rgba(125, 211, 252, 0.14); color: #7dd3fc; }

        /* ---- THE PAGE CARD. Fixed in both modes; see the contract above. ---- */
        .es-page-card {
            background-color: #ffffff;
            color: #111827;
            border: 1px solid rgba(17, 24, 39, 0.12);
            border-radius: 1.25rem;
            box-shadow: 0 18px 45px rgba(17, 24, 39, 0.16);
        }
        .dark .es-page-card { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-page-card-muted { color: #4b5563; }
        .es-page-card-accent { color: #0369a1; }
        .es-page-flyer {
            background-image: linear-gradient(135deg, #0c4a6e 0%, #0369a1 45%, #0891b2 100%);
            color: #ffffff;
        }
        .es-page-map {
            background-image: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 60%, #ecfeff 100%);
            border: 1px solid rgba(3, 105, 161, 0.18);
        }

        #es-page a:focus-visible,
        #es-page summary:focus-visible {
            outline: 2px solid #0369a1;
            outline-offset: 2px;
        }
        .dark #es-page a:focus-visible,
        .dark #es-page summary:focus-visible { outline-color: #7dd3fc; }
    </style>

    <div id="es-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the page a guest meets                              -->
        <!-- ============================================================ -->
        <section id="top" class="es-hero noise relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 22% 68%, rgba(37, 99, 235, 0.2), rgba(37, 99, 235, 0) 65%);"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 30%, rgba(6, 182, 212, 0.18), rgba(6, 182, 212, 0) 65%);"></div>
                <div class="grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="min-w-0">
                        <h1 class="es-balance text-4xl font-black tracking-tight text-gray-900 dark:text-white md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-page-tag mb-4">Event landing page &middot; free for every event</x-marketing.hero-eyebrow>
                            Every event gets <span class="text-gradient-page">a page worth sharing.</span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                            Publish an event and it has a page of its own: the flyer, the date and time, the venue with a map, and a button to register or buy a ticket. There is no page to design, no plugin to install and no template to fill in twice.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="es-page-btn inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold transition-colors">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/creating-events') }}" class="es-page-ghost inline-flex items-center gap-2 rounded-xl border px-6 py-3 font-semibold transition-colors">
                                Read the guide
                            </a>
                        </div>
                    </div>

                    <div class="es-page-card mx-auto w-full max-w-sm overflow-hidden" data-reveal="panel" style="--reveal-delay: 0.1s;" aria-label="Example event landing page">
                        <div class="es-page-flyer flex h-40 flex-col justify-end p-5">
                            <p class="text-xs font-semibold uppercase tracking-widest opacity-80">Live at Riverside Hall</p>
                            <p class="mt-1 text-2xl font-black leading-tight">The Paper Lanterns</p>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 flex-none flex-col items-center justify-center rounded-xl border border-gray-200">
                                    <span class="es-page-card-accent text-[10px] font-bold uppercase leading-none">Oct</span>
                                    <span class="text-lg font-bold leading-none">10</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Saturday, 7:30 PM</p>
                                    <p class="es-page-card-muted text-xs">Riverside Hall, 12 Mill Street</p>
                                </div>
                            </div>
                            <div class="es-page-map flex h-16 items-center justify-center gap-2 rounded-lg text-xs font-semibold text-sky-800">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Riverside Hall
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <span class="es-page-btn rounded-lg py-2 text-center text-sm font-semibold">Buy Tickets</span>
                                <span class="rounded-lg border border-gray-300 py-2 text-center text-sm font-semibold text-gray-700">Add to Calendar</span>
                            </div>
                            <p class="es-page-card-muted text-xs leading-relaxed">Four-piece folk with a string section, touring the new record. Doors at 7, support at 7:30.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What is on the page                                       -->
        <!-- ============================================================ -->
        <section id="anatomy" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-page-tag mb-4" data-reveal>What is on the page</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Everything a guest asks <span class="text-gradient-page">before they come</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                        What is it, when is it, where is it, and how do I get in. The page answers each one from the event you already entered.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="60">
                    @foreach ($anatomy as $partIndex => [$partTitle, $partBody, $partTier])
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <span class="es-page-num flex h-8 w-8 flex-none items-center justify-center rounded-full text-sm font-bold" aria-hidden="true">{{ $partIndex + 1 }}</span>
                                <span class="es-page-pill">{{ $partTier }}</span>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $partTitle }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $partBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Found, and shared                                         -->
        <!-- ============================================================ -->
        @php
            $findable = [
                ['A title that says when and where', 'The page title carries the event\'s name, its date and its venue, and the description comes from your own words, so a search result or a shared link says what the event is before anyone taps it.'],
                ['Event structured data', 'Every event page describes itself to search engines as an event: the date, the place and, where the page sells them, the tickets. It says only what the page shows, so it never promises more than the page does.'],
                ['A preview with your picture', 'Share the link and the preview uses your flyer, or a performer\'s, the venue\'s or your schedule\'s own picture. With none of them it shows no picture, never an advert of ours.'],
            ];
        @endphp
        <section id="found" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-page-tag mb-4" data-reveal>Found, and shared</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Built to be found <span class="text-gradient-page">and passed on</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                        Confirm your schedule's email address and its events are listed in the sitemap search engines read. A recurring event keeps one page for the whole series, so its link gathers the attention rather than splitting it week by week.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="80">
                    @foreach ($findable as [$findTitle, $findBody])
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $findTitle }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $findBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Make it yours                                             -->
        <!-- ============================================================ -->
        @php
            $yours = [
                ['Your colours and type', 'Pick the colours, the font, a background and a header image for your schedule, and every event page follows them.', 'Free', '/docs/schedule-styling'],
                ['Your own CSS', 'Change the finer details of the page with a stylesheet of your own.', 'Pro', '/features/custom-css'],
                ['No branding of ours', 'Take the Event Schedule credit off your pages, your embeds and your emails.', 'Pro', '/features/white-label'],
                ['Your own domain', 'Put the whole schedule, event pages included, on an address you own.', 'Enterprise', '/features/custom-domain'],
            ];
        @endphp
        <section id="yours" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-page-tag mb-4" data-reveal>Make it yours</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Your page, <span class="text-gradient-page">in your colours</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                    @foreach ($yours as [$yoursTitle, $yoursBody, $yoursTier, $yoursPath])
                        <a href="{{ marketing_url($yoursPath) }}" class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40" data-reveal="panel">
                            <span class="es-page-pill self-start">{{ $yoursTier }}</span>
                            <h3 class="mt-3 text-base font-bold text-gray-900 dark:text-white">{{ $yoursTitle }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $yoursBody }}</p>
                            <span class="es-page-accent mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold transition-all group-hover:gap-2">
                                Learn more
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </a>
                    @endforeach
                </div>

                <p class="mx-auto mt-10 max-w-3xl text-center text-gray-600 dark:text-gray-400" data-reveal>
                    Already have a website? Put your whole calendar on it with the free <x-link href="{{ marketing_url('/features/embed-calendar') }}">calendar embed</x-link> and every event still opens its own page.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. FAQ                                                       -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$landingFaqs" />
        <section id="faq" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-balance mb-10 text-center text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Event page questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($landingFaqs as $faq)
                        <details name="faq" class="group rounded-2xl border border-gray-200 bg-white p-5 transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40" data-reveal="panel">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-gray-900 dark:text-white">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                            @if (! empty($faq['link']))
                                <p class="mt-3"><a href="{{ $faq['link'][0] }}" class="es-page-accent inline-block text-sm font-semibold hover:underline">{{ $faq['link'][1] }}</a></p>
                            @endif
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                        <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    </div>

                    <div class="relative z-10">
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                            Your next event, <span class="text-gradient-page">one link away</span>
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            A landing page for every event, free, with registration built in and paid tickets on Pro.
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
    </div>

    <x-marketing.related-pages />

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
