<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.features_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.features_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Features</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Features",
        "description": "Discover all the features that make Event Schedule a simple, powerful way to manage events, sell tickets, and engage your audience.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Event Ticketing with QR Check-in",
            "Passes and Subscriptions",
            "Gift Cards",
            "Appointment Booking",
            "Promo/Discount Codes",
            "Ticket Waitlist",
            "Check-in Dashboard",
            "Free Event Registration",
            "Sales CSV Export",
            "Sale Notification Emails",
            "Custom Fields",
            "AI Event Parsing",
            "AI Flyer Generation",
            "WhatsApp Event Creation",
            "Google Calendar Sync",
            "Outlook and Microsoft 365 Sync",
            "CalDAV Sync",
            "iCal Download",
            "Recurring Events",
            "Sub-schedules",
            "Online Events",
            "Availability Management",
            "Team Scheduling",
            "Email Newsletters",
            "Event Graphics",
            "Event Boost Ad Campaigns",
            "Embed Calendar",
            "Embed Ticket Widget",
            "Fan Videos & Comments",
            "Event Polls",
            "Post-Event Feedback",
            "Carpool Matching",
            "Analytics Dashboard",
            "Private Events",
            "Custom Domains",
            "White Label Branding",
            "Custom CSS",
            "Custom Labels",
            "Backup and Restore",
            "Open Source, REST API and Webhooks"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free plan available"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-features {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-features {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-features {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Chapter aurora: the shared .es-aurora opacity reads as a gray smudge on
           white grounds, so light mode runs it much softer than dark. */
        .es-ch-aurora { opacity: 0.3; }
        .dark .es-ch-aurora { opacity: 0.55; }

        /* Chapter hairline draws itself in on reveal. The transition lives on the
           always-active rule and only the pre-state is gated, so no-JS visitors,
           crawlers and reduced-motion users see it at full width. */
        .es-ch-rule {
            transform-origin: left;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.15s;
        }
        [dir="rtl"] .es-ch-rule { transform-origin: right; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-ch-rule { transform: scaleX(0); }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero                                                        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(78svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Everything you need</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Every feature, in</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-features">five chapters</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Everything you need to fill seats, from calendars and ticketing to newsletters and analytics.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#sell" class="group inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Explore features
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Feature marquee: breadth at a glance                        -->
    <!-- ============================================================ -->
    @php
        $marqueeRows = [
            [
                ['QR check-in', 'bg-sky-500', route('marketing.ticketing')],
                ['AI import', 'bg-blue-500', route('marketing.ai')],
                ['Newsletters', 'bg-cyan-500', route('marketing.newsletters')],
                ['Gift cards', 'bg-emerald-500', route('marketing.gift_cards')],
                ['Calendar sync', 'bg-blue-400', route('marketing.calendar_sync')],
                ['Event graphics', 'bg-orange-500', route('marketing.event_graphics')],
                ['Event polls', 'bg-sky-500', route('marketing.polls')],
                ['Analytics', 'bg-emerald-500', route('marketing.analytics')],
            ],
            [
                ['Appointments', 'bg-blue-500', route('marketing.appointments')],
                ['Recurring events', 'bg-teal-500', route('marketing.recurring_events')],
                ['Custom domains', 'bg-teal-500', route('marketing.custom_domain')],
                ['Boost ads', 'bg-amber-500', route('marketing.boost')],
                ['Fan videos', 'bg-cyan-500', route('marketing.fan_videos')],
                ['Team scheduling', 'bg-cyan-500', route('marketing.team_scheduling')],
                ['Online events', 'bg-sky-500', route('marketing.online_events')],
                ['Sub-schedules', 'bg-blue-400', route('marketing.sub_schedules')],
                ['Reserved seating', 'bg-emerald-500', route('marketing.allocated_seating')],
            ],
        ];
    @endphp
    <section class="relative overflow-hidden border-y border-gray-200 bg-white py-10 dark:border-white/10 dark:bg-[#0a0a0f]" aria-label="Feature highlights">
        <h2 class="sr-only">Feature highlights</h2>
        <div class="es-marquee-mask space-y-4">
            @foreach ($marqueeRows as $rowIndex => $row)
                <div class="es-marquee" data-marquee="{{ $rowIndex === 0 ? '1' : '-1' }}">
                    <div class="es-marquee-track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($row as [$label, $dot, $href])
                                <a href="{{ $href }}" @if ($i === 1) aria-hidden="true" tabindex="-1" @endif class="flex items-center gap-2.5 rounded-full border border-gray-200/70 bg-gray-100/80 px-6 py-3 text-lg font-semibold text-gray-800 transition-colors hover:text-blue-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:text-blue-400">
                                    <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                    {{ $label }}
                                </a>
                            @endforeach
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Chapter 01: Sell                                            -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="01"
        id="sell"
        accent="blue"
        title="Take the money"
        lede="Tickets, passes, gift cards and paid bookings, with zero platform fees and payouts straight to your own Stripe account."
        ground="white" />

    <x-marketing.feature-banner
        :href="route('marketing.ticketing')"
        accent="sky"
        badge="Ticketing"
        heading="Sell tickets online"
        lede="Multiple ticket types, waitlist, and a live check-in dashboard. Accept payments with Stripe. Zero platform fees."
        :chips="['Zero fees', 'QR check-ins', 'Stripe payments', 'Check-in dashboard', 'Waitlist', 'Promo codes', 'Sales export', 'Free event RSVP', 'Reserved seating']"
        :lead="true"
        frame="browser"
        frame-url="yourvenue.eventschedule.com/tickets"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
        </x-slot>

        <div class="space-y-3">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">Early Bird</div>
                    <div class="text-xs text-emerald-700 dark:text-emerald-400">50 remaining</div>
                </div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">$18</div>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">General</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">142 sold</div>
                </div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50 p-3 dark:border-sky-400/30 dark:bg-sky-500/15">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">VIP</div>
                    <div class="text-xs text-sky-700 dark:text-sky-300">38 sold</div>
                </div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
            </div>
            <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-white/10">
                <span class="text-xs text-gray-500 dark:text-gray-400">Platform fee</span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ plan_price(0) }}</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.appointments')"
        accent="blue"
        badge="Appointments"
        heading="Let guests book your time"
        lede="Create bookable appointment types with weekly hours and share one link. Guests pick an open time in their own timezone, free or paid."
        :chips="['Weekly hours', 'Free or paid', 'No double-booking', 'Reminders']"
        :flip="true"
        frame="phone"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-slot>

        <div class="mb-4 flex gap-2">
            <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 py-2 text-center dark:border-white/10 dark:bg-white/5">
                <div class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Mon</div>
                <div class="text-lg font-black leading-none text-gray-900 dark:text-white">14</div>
            </div>
            <div class="flex-1 rounded-xl border border-blue-200 bg-blue-50 py-2 text-center dark:border-blue-400/30 dark:bg-blue-500/15">
                <div class="text-[10px] font-bold uppercase text-blue-700 dark:text-blue-300">Tue</div>
                <div class="text-lg font-black leading-none text-gray-900 dark:text-white">15</div>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 py-2 text-center dark:border-white/10 dark:bg-white/5">
                <div class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Wed</div>
                <div class="text-lg font-black leading-none text-gray-900 dark:text-white">16</div>
            </div>
        </div>
        <div class="space-y-2.5">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <div class="font-medium text-gray-900 dark:text-white">9:00 AM</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Open</div>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50 p-3 dark:border-blue-400/30 dark:bg-blue-500/15">
                <div class="font-medium text-gray-900 dark:text-white">3:00 PM</div>
                <div class="text-xs font-semibold text-blue-600 dark:text-blue-300">Selected</div>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <div class="font-medium text-gray-900 dark:text-white">4:30 PM</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Open</div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.gift_cards')"
        accent="cyan"
        badge="Gift Cards"
        heading="Sell gift cards"
        lede="Let customers buy a gift card for someone else and redeem it toward tickets for any event on your schedule. Balance-tracked and delivered by email."
        :chips="['Your denominations', 'Email delivery', 'Balance carries over']"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
        </x-slot>

        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Gift Card</span>
            </div>
            <span class="text-xl font-bold text-gray-900 dark:text-white">$100</span>
        </div>
        <div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
            <div class="mb-1 text-xs text-gray-500 dark:text-gray-400">Code</div>
            <div dir="ltr" class="font-mono text-sm text-cyan-700 dark:text-cyan-300">8Q2K-7MRT-4XPW</div>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-600 dark:text-gray-400">Balance</span>
            <span class="font-medium text-emerald-700 dark:text-emerald-400">$100 available</span>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.custom_fields')"
        accent="amber"
        badge="Custom Fields"
        heading="Collect buyer info"
        lede="Gather dietary preferences, t-shirt sizes, or any info you need from ticket buyers with flexible form fields."
        :chips="['Multiple field types', 'Required or optional']"
        :flip="true"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </x-slot>

        <div class="space-y-3">
            <div>
                <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Company Name</div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-white">Acme Corp</div>
            </div>
            <div>
                <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">T-Shirt Size</div>
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <span>Large</span>
                    <svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
            <div>
                <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Vegetarian?</div>
                <div class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-400/30 dark:bg-amber-500/15 dark:text-amber-300">
                    <span>Yes</span>
                    <svg aria-hidden="true" class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <!-- Punctuation band A: the fee claim, right after the selling chapter -->
    <section class="relative overflow-hidden bg-white py-12 dark:bg-[#0a0a0f] lg:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-600 p-8 shadow-xl shadow-blue-500/20 md:p-12" data-reveal="panel">
                <div class="grid grid-cols-1 gap-8 text-center md:grid-cols-3">
                    <div>
                        <div class="mb-2 text-3xl font-bold text-white md:text-4xl">100%</div>
                        <div class="text-sm text-blue-100">Free and open source</div>
                    </div>
                    <div>
                        <div class="mb-2 text-3xl font-bold text-white md:text-4xl">0%</div>
                        <div class="text-sm text-blue-100">Platform fees on tickets</div>
                    </div>
                    <div>
                        <div class="mb-2 text-3xl font-bold text-white md:text-4xl">12</div>
                        <div class="text-sm text-blue-100">Languages supported</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Chapter 02: Schedule                                        -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="02"
        id="schedule"
        accent="sky"
        title="Keep the calendar straight"
        lede="Two-way sync with the calendar you already live in, repeating events that build themselves, and AI that turns a flyer into a listing."
        ground="gray" />

    <x-marketing.feature-banner
        :href="route('marketing.calendar_sync')"
        accent="blue"
        badge="Calendar Sync"
        heading="Two-way sync"
        lede="Sync with Google Calendar, Outlook and any CalDAV server automatically. Google and Outlook push changes back within minutes; CalDAV is polled every fifteen."
        :chips="['Two-way sync', 'Free on every plan', 'Google, Outlook, CalDAV']"
        :lead="true"
        frame="browser"
        frame-url="calendar.google.com"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>

        <div class="flex items-center gap-4">
            <div class="flex-1 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-400/30 dark:bg-blue-500/15">
                <div class="mb-2 text-center text-xs text-blue-600 dark:text-blue-300">Event Schedule</div>
                <div class="space-y-1.5">
                    <div class="h-2 rounded bg-blue-400/40"></div>
                    <div class="h-2 w-3/4 rounded bg-blue-400/40"></div>
                    <div class="h-2 w-1/2 rounded bg-blue-400/40"></div>
                </div>
            </div>
            <div class="flex flex-col items-center gap-1">
                <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <svg aria-hidden="true" class="h-6 w-6 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                <div class="mb-2 text-center text-xs text-gray-600 dark:text-gray-300">Google Calendar</div>
                <div class="space-y-1.5">
                    <div class="h-2 rounded bg-blue-400/50"></div>
                    <div class="h-2 w-3/4 rounded bg-green-400/50"></div>
                    <div class="h-2 w-1/2 rounded bg-yellow-400/50"></div>
                </div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.recurring_events')"
        accent="teal"
        badge="Recurring Events"
        heading="Automate your week"
        lede="Set events to repeat daily, weekly, biweekly, monthly or yearly. Three end conditions, per-date ticket inventory, and an iCal feed that unrolls every date."
        :chips="['Daily to yearly', 'Per-date tickets', 'iCal feed']"
        :flip="true"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </x-slot>

        <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Repeat on</div>
        <div class="mb-4 flex gap-1.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">S</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-[10px] font-medium text-white">M</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">T</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-[10px] font-medium text-white">W</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">T</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-[10px] font-medium text-white">F</div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">S</div>
        </div>
        <div class="inline-flex items-center gap-1.5 rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs text-teal-700 dark:border-teal-400/30 dark:bg-teal-500/15 dark:text-teal-300">
            <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Repeats: Weekly
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.sub_schedules')"
        accent="cyan"
        badge="Sub-schedules"
        heading="Organize your events"
        lede="Create sub-schedules to sort events by stage, series, or any way you like. Each one gets a colour, a visitor filter and its own URL."
        :chips="['Colour-coded', 'Visitor filter', 'Its own URL']"
        frame="browser"
        frame-url="thevenue.eventschedule.com"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </x-slot>

        <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Sub-schedules</div>
        <div class="space-y-2">
            <div class="flex items-center gap-2 rounded-lg border border-cyan-200 bg-cyan-50 p-2 dark:border-cyan-500/30 dark:bg-cyan-500/15">
                <div class="h-2 w-2 rounded-full bg-cyan-400"></div>
                <span class="text-sm text-gray-900 dark:text-white">Main Stage</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                <div class="h-2 w-2 rounded-full bg-cyan-400"></div>
                <span class="text-sm text-gray-600 dark:text-gray-300">Acoustic Room</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                <div class="h-2 w-2 rounded-full bg-sky-400"></div>
                <span class="text-sm text-gray-600 dark:text-gray-300">Outdoor Patio</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                <div class="h-2 w-2 rounded-full bg-blue-400"></div>
                <span class="text-sm text-gray-600 dark:text-gray-300">Jazz Lounge</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.online_events')"
        accent="sky"
        badge="Online Events"
        heading="Go live, anywhere"
        lede="Tick Online on any event and paste the link people join on. Zoom, YouTube, Teams or your own page: it is a link, not an integration."
        :chips="['Virtual events', 'Any platform link']"
        :flip="true"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </x-slot>

        <div class="mb-4 flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-300">Online Event</span>
            <div class="relative h-5 w-10 rounded-full bg-sky-500">
                <div class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow ltr:right-0.5 rtl:left-0.5"></div>
            </div>
        </div>
        <div>
            <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Event link</div>
            <div class="flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-700 dark:border-sky-400/30 dark:bg-sky-500/15 dark:text-sky-300">
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                </svg>
                <span dir="ltr" class="truncate">zoom.us/j/123...</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.ai')"
        accent="blue"
        badge="AI-Powered"
        heading="AI-powered features"
        lede="Parse text and images, generate flyers and descriptions, create your brand style, translate to 12 languages, and create events via WhatsApp."
        :chips="['Smart import', 'Style generation', 'Content generation', 'Instant translation', 'WhatsApp']"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
        </x-slot>

        <div class="mb-2 text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400">Paste or drop</div>
        <div dir="ltr" class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs leading-relaxed text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
            Jazz Night at Blue Note<br>
            Friday, March 15 at 8pm<br>
            Tickets: $25
        </div>
        <div class="mb-3 flex justify-center">
            <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-400/30 dark:bg-blue-500/15">
            <div class="mb-2 text-[10px] uppercase tracking-wider text-blue-600 dark:text-blue-300">Extracted</div>
            <div class="space-y-1.5 text-sm">
                <div class="es-ai-field flex justify-between" style="--i: 0;"><span class="text-gray-600 dark:text-gray-400">Name:</span><span class="text-gray-900 dark:text-white">Jazz Night</span></div>
                <div class="es-ai-field flex justify-between" style="--i: 1;"><span class="text-gray-600 dark:text-gray-400">Date:</span><span class="text-gray-900 dark:text-white">Mar 15, 8 PM</span></div>
                <div class="es-ai-field flex justify-between" style="--i: 2;"><span class="text-gray-600 dark:text-gray-400">Venue:</span><span class="text-gray-900 dark:text-white">Blue Note</span></div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <!-- Punctuation band B: the integrations the page used to orphan entirely -->
    <section class="relative overflow-hidden border-y border-gray-200 bg-white py-14 dark:border-white/10 dark:bg-[#0a0a0f] lg:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="es-balance mb-2 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>
                Works with what you already use
            </h2>
            <p class="mx-auto mb-8 max-w-2xl text-gray-500 dark:text-gray-400" data-reveal>
                Connect the calendar and payment tools your team already runs on.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3" data-reveal-group="60">
                @php
                    $integrations = [
                        ['google', 'Google Calendar', marketing_url('/google-calendar')],
                        ['outlook', 'Outlook', marketing_url('/outlook-calendar')],
                        ['caldav', 'CalDAV', marketing_url('/caldav')],
                        ['stripe', 'Stripe', marketing_url('/stripe')],
                        ['invoiceninja', 'Invoice Ninja', marketing_url('/invoiceninja')],
                    ];
                @endphp
                @foreach ($integrations as [$logo, $label, $href])
                    <a href="{{ $href }}" data-reveal
                       class="group inline-flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40">
                        @include('marketing.partials.integration-logo', ['name' => $logo, 'class' => 'h-7 w-7'])
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 dark:text-gray-200 dark:group-hover:text-blue-400">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
            <p class="mt-8" data-reveal>
                <a href="{{ route('marketing.integrations') }}" class="group inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-all hover:gap-2 dark:text-blue-400">
                    See all integrations
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Chapter 03: Promote                                         -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="03"
        id="promote"
        accent="cyan"
        title="Fill the room"
        lede="Publishing an event already tells the people who subscribed. Newsletters, shareable graphics and Meta ads cover everyone else, without opening a single ad manager."
        ground="gray" />

    <x-marketing.feature-banner
        :href="route('marketing.newsletters')"
        accent="sky"
        badge="Newsletters"
        heading="Engage your audience"
        lede="Subscribers get a digest automatically when you publish new events. Write the rest yourself in a drag-and-drop builder, with segments, A/B testing and open and click tracking."
        :chips="['Automatic new-event digest', 'Drag-and-drop builder', 'A/B testing', 'Open and click tracking']"
        :lead="true"
        :flip="true"
        frame="browser"
        frame-url="eventschedule.com/admin/newsletters"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </x-slot>

        <div class="mb-3">
            <div class="mb-1 flex justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400">Open rate</span>
                <span class="font-medium text-sky-700 dark:text-sky-300">42%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div class="es-bar h-full rounded-full bg-sky-500" style="width: 42%; --bd: 0.1s;"></div>
            </div>
        </div>
        <div class="mb-4">
            <div class="mb-1 flex justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400">Click rate</span>
                <span class="font-medium text-cyan-700 dark:text-cyan-300">18%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div class="es-bar h-full rounded-full bg-cyan-500" style="width: 18%; --bd: 0.25s;"></div>
            </div>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-3 text-center dark:border-white/10">
            <div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">800</div>
                <div class="text-[10px] text-gray-500 dark:text-gray-400">Sent</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">336</div>
                <div class="text-[10px] text-gray-500 dark:text-gray-400">Opens</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">144</div>
                <div class="text-[10px] text-gray-500 dark:text-gray-400">Clicks</div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.event_graphics')"
        accent="orange"
        badge="Event Graphics"
        heading="Share your events everywhere"
        lede="Auto-generate shareable images and formatted text for upcoming events. Ready for Instagram, WhatsApp, email, and more."
        :chips="['Auto-generated', 'Social-ready', 'Multiple formats', 'AI flyer generation', 'AI style generation']"
        frame="phone"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>

        <div class="mb-3 rounded-lg bg-gradient-to-br from-orange-400 to-amber-500 p-4">
            <div class="mb-2 flex items-center gap-2">
                <div class="h-3 w-3 rounded bg-white/30"></div>
                <div class="h-2 w-16 rounded bg-white/40"></div>
            </div>
            <div class="mb-1 text-sm font-bold text-white">Summer Jazz Night</div>
            <div class="text-[10px] text-white/80">Sat, Jul 12 at 8 PM</div>
            <div class="text-[10px] text-white/60">Blue Note Jazz Club</div>
        </div>
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 p-2 dark:border-orange-400/30 dark:bg-orange-500/15">
                <svg aria-hidden="true" class="h-3.5 w-3.5 text-orange-600 dark:text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-[10px] text-orange-700 dark:text-orange-300">Image graphic</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                <svg aria-hidden="true" class="h-3.5 w-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-[10px] text-gray-600 dark:text-gray-300">Formatted text</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.boost')"
        accent="amber"
        badge="Event Boost"
        heading="Amplify your events"
        lede="Turn any event into a live Facebook and Instagram ad. Set your budget, pick your audience, and launch in minutes. No ad manager required."
        :chips="['Facebook & Instagram', 'Smart targeting', 'Reach, clicks and spend']"
        :flip="true"
        frame="phone"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
        </x-slot>

        <div class="mb-3 flex items-center gap-2">
            <div class="h-5 w-5 rounded-full bg-gradient-to-br from-orange-400 to-amber-500"></div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400">Sponsored</div>
        </div>
        <div class="mb-3 rounded-lg bg-gradient-to-br from-orange-400 to-amber-500 p-4 text-center">
            <div class="text-sm font-bold text-white">Summer Music Fest</div>
            <div class="mt-0.5 text-[10px] text-white/80">Sat, Jul 12 at 6 PM</div>
        </div>
        <div class="mb-3">
            <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Budget</div>
            <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div class="es-bar h-full w-3/5 rounded-full bg-gradient-to-r from-orange-400 to-amber-500" style="--bd: 0.15s;"></div>
            </div>
            <div class="mt-1 text-[10px] font-medium text-amber-700 dark:text-amber-300">$60 / 7 days</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 text-center dark:border-white/10 dark:bg-white/5">
            <div class="text-[10px] text-gray-500 dark:text-gray-400">Est. reach</div>
            <div class="text-sm font-bold text-amber-700 dark:text-amber-300">2,400 - 6,800</div>
        </div>
    </x-marketing.feature-banner>

    <!-- ============================================================ -->
    <!-- Chapter 04: Engage                                          -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="04"
        id="engage"
        accent="emerald"
        title="Turn a crowd into a following"
        lede="Fan videos, polls, star ratings and privacy-first analytics, so the people who showed up once have a reason to come back."
        ground="white" />

    <x-marketing.feature-banner
        :href="route('marketing.fan_videos')"
        accent="cyan"
        badge="Fan Engagement"
        heading="Build community around events"
        lede="Fans add YouTube videos, photos and comments to your events, down to an individual agenda item. Everything waits in an approval queue, and a name and email is enough to contribute."
        :chips="['YouTube videos', 'Photos', 'Comments', 'Approval queue', 'Per agenda item']"
        :lead="true"
        frame="phone"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </x-slot>

        <div class="mb-1 text-xs font-medium text-gray-900 dark:text-white">Jazz Night</div>
        <div class="mb-3 text-[10px] text-gray-500 dark:text-gray-400">Fri, Mar 15 at 8 PM</div>
        <div class="mb-3 flex items-center justify-center rounded-lg bg-gray-900 p-5 dark:bg-black/60">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-red-600">
                <svg aria-hidden="true" class="ms-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex items-start gap-2">
                <div class="h-5 w-5 shrink-0 rounded-full bg-cyan-300 dark:bg-cyan-500/40"></div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-[10px] text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">Amazing show!</div>
            </div>
            <div class="flex items-start gap-2">
                <div class="h-5 w-5 shrink-0 rounded-full bg-orange-300 dark:bg-orange-500/40"></div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-[10px] text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">Best night ever</div>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.polls')"
        accent="blue"
        badge="Event Polls"
        heading="Let your audience decide"
        lede="Add polls to any event. Signed-in guests mark one choice, and the count comes back the moment they vote. A simple way to boost engagement."
        :chips="['Multiple choice', 'One vote per account', 'Results on vote']"
        :flip="true"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
        </x-slot>

        <div class="mb-3 text-sm font-medium text-gray-900 dark:text-white">What genre next week?</div>
        <div class="space-y-2.5">
            <div>
                <div class="mb-1 flex justify-between text-[11px]">
                    <span class="text-gray-600 dark:text-gray-300">Jazz</span>
                    <span class="font-medium text-blue-600 dark:text-blue-300">45%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="es-bar h-full w-[45%] rounded-full bg-gradient-to-r from-blue-400 to-cyan-400" style="--bd: 0.1s;"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-[11px]">
                    <span class="text-gray-600 dark:text-gray-300">Rock</span>
                    <span class="font-medium text-blue-600 dark:text-blue-300">35%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="es-bar h-full w-[35%] rounded-full bg-gradient-to-r from-blue-400 to-cyan-400" style="--bd: 0.22s;"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-[11px]">
                    <span class="text-gray-600 dark:text-gray-300">Blues</span>
                    <span class="font-medium text-blue-600 dark:text-blue-300">20%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="es-bar h-full w-[20%] rounded-full bg-gradient-to-r from-blue-400 to-cyan-400" style="--bd: 0.34s;"></div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-center text-[10px] text-gray-500 dark:text-gray-400">42 votes</div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.feedback')"
        accent="amber"
        badge="Post-Event Feedback"
        heading="Know what your audience thinks"
        lede="Automatically email attendees after events to collect star ratings and comments. View results in your admin panel with average ratings and response rates."
        :chips="['1-5 star ratings', 'Automatic emails', 'CSV export']"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
            </svg>
        </x-slot>

        <div class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Event Feedback</div>
        <div class="mb-4 flex items-center gap-1">
            @for ($s = 0; $s < 4; $s++)
                <svg aria-hidden="true" class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
            <svg aria-hidden="true" class="h-5 w-5 text-gray-300 dark:text-white/20" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span class="ms-1 text-xs text-gray-600 dark:text-gray-300">4.0</span>
        </div>
        <div class="space-y-2">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                <div class="mb-1 text-[10px] text-amber-700 dark:text-amber-400">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">"Amazing show!"</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                <div class="mb-1 text-[10px] text-amber-700 dark:text-amber-400">&#9733;&#9733;&#9733;&#9733;<span class="text-gray-300 dark:text-white/20">&#9733;</span></div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">"Great venue, will return"</div>
            </div>
        </div>
        <div class="mt-3 text-center text-[10px] text-gray-500 dark:text-gray-400">28 responses</div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.analytics')"
        accent="emerald"
        badge="Analytics"
        heading="Know your audience"
        lede="Three tabs: web traffic, revenue and check-ins. Page views, referrers, UTM campaigns, devices and your best-earning events, with no third-party tracker involved."
        :chips="['Web, revenue, check-ins', 'UTM campaigns', 'No external services']"
        :flip="true"
        frame="browser"
        frame-url="eventschedule.com/admin/analytics"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </x-slot>

        <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Views this week</div>
        <div class="flex h-28 items-end justify-between gap-2">
            <div class="es-bar w-full rounded-t bg-emerald-500/40" style="height: 40%; --bd: 0.05s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500/50" style="height: 55%; --bd: 0.12s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500/60" style="height: 45%; --bd: 0.19s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500/70" style="height: 70%; --bd: 0.26s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500/80" style="height: 60%; --bd: 0.33s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500/90" style="height: 85%; --bd: 0.4s;"></div>
            <div class="es-bar w-full rounded-t bg-emerald-500" style="height: 100%; --bd: 0.47s;"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
        </div>
    </x-marketing.feature-banner>

    <!-- ============================================================ -->
    <!-- Chapter 05: Own it                                          -->
    <!-- ============================================================ -->
    <x-marketing.feature-chapter
        number="05"
        id="own-it"
        accent="amber"
        title="Make it yours"
        lede="Your domain, your branding, your team, your server. Event Schedule is open source, so nothing here is locked behind us."
        ground="white" />

    <x-marketing.feature-banner
        :href="route('marketing.custom_domain')"
        accent="teal"
        badge="Custom Domains"
        heading="Your domain, your brand"
        lede="Use your own domain name for your schedule. Point one DNS record at us and the certificate is issued automatically."
        :chips="['Automatic SSL', 'Branded URLs', 'Enterprise plan']"
        :lead="true"
        frame="browser"
        frame-url="events.myband.com"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
        </x-slot>

        <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Before</div>
        <div dir="ltr" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-sm text-gray-600 line-through dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
            myband.eventschedule.com
        </div>
        <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">After</div>
        <div dir="ltr" class="rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 font-mono text-sm font-medium text-teal-700 dark:border-teal-500/30 dark:bg-teal-500/15 dark:text-teal-300">
            events.myband.com
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[10px] text-teal-700 dark:text-teal-400">
            <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            SSL certificate included
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.private_events')"
        accent="yellow"
        badge="Private Events"
        heading="Control who sees what"
        lede="Every event carries one of four visibility states, set on the event itself, so public nights and members-only ones live on the same schedule."
        :chips="['Public', 'Draft', 'Internal', 'Unlisted plus password']"
        :flip="true"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </x-slot>

        @php
            // The four states Event::visibilityState() can return, with the app's own
            // descriptions from messages.visibility_*_desc.
            $visibilityStates = [
                ['Public', 'Anyone can find it', 'emerald', false],
                ['Draft', 'Members only, until you publish', 'gray', false],
                ['Internal', 'Members only, never public', 'sky', true],
                ['Unlisted', 'Link only, password optional', 'yellow', true],
            ];
            $visibilityDot = [
                'emerald' => 'bg-emerald-400',
                'gray' => 'bg-gray-400',
                'sky' => 'bg-sky-400',
                'yellow' => 'bg-yellow-400',
            ];
        @endphp
        <div class="space-y-1.5">
            @foreach ($visibilityStates as [$stateName, $stateDesc, $stateHue, $stateEnterprise])
                <div @class([
                    'flex items-center gap-2 rounded-lg p-2',
                    'border border-yellow-200 bg-yellow-50 dark:border-yellow-400/30 dark:bg-yellow-500/15' => $stateHue === 'yellow',
                    'bg-gray-50 dark:bg-white/5' => $stateHue !== 'yellow',
                ])>
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $visibilityDot[$stateHue] }}" aria-hidden="true"></span>
                    <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-200">{{ $stateName }}</span>
                    <span class="flex-1 text-[10px] text-gray-500 dark:text-gray-400">{{ $stateDesc }}</span>
                    @if ($stateEnterprise)
                        <span class="rounded-full bg-gray-200 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">Ent</span>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            <div class="mb-1 text-[10px] text-gray-500 dark:text-gray-400">Password, on an unlisted event</div>
            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-white">
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="tracking-widest">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="route('marketing.team_scheduling')"
        accent="cyan"
        badge="Team Scheduling"
        heading="Collaborate together"
        lede="Invite people by email as admins or viewers and run the schedule together. The free plan is a team of one; Enterprise goes up to five."
        :chips="['Invite by email', 'Owner, admin, viewer']"
        ground="white">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </x-slot>

        <div class="space-y-2">
            <div class="flex items-center gap-2 rounded-lg border border-cyan-200 bg-cyan-50 p-2.5 dark:border-cyan-500/30 dark:bg-cyan-500/15">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 text-xs font-semibold text-white">JD</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-gray-900 dark:text-white">John Doe</div>
                </div>
                <span class="rounded bg-white px-1.5 py-0.5 text-[10px] text-cyan-700 dark:bg-white/10 dark:text-cyan-300">Owner</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2.5 dark:bg-white/5">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-xs font-semibold text-white">AS</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-gray-600 dark:text-gray-300">Alice Smith</div>
                </div>
                <span class="rounded bg-teal-100 px-1.5 py-0.5 text-[10px] text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Admin</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2.5 dark:bg-white/5">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-green-500 text-xs font-semibold text-white">BJ</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-gray-600 dark:text-gray-300">Bob Jones</div>
                </div>
                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Viewer</span>
            </div>
        </div>
    </x-marketing.feature-banner>

    <x-marketing.feature-banner
        :href="marketing_url('/open-source')"
        accent="gray"
        badge="Open Source"
        heading="Free and open source"
        lede="100% open source under the Attribution Assurance License (AAL). Selfhost on your own server or integrate with our REST API."
        :chips="['Selfhost', 'REST API', 'Webhooks']"
        :flip="true"
        frame="browser"
        ground="gray">
        <x-slot name="badgeIcon">
            <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
        </x-slot>

        <div dir="ltr" class="space-y-1 font-mono text-xs">
            <div class="text-gray-600 dark:text-gray-400">$ git clone</div>
            <div class="break-all leading-tight text-cyan-700 dark:text-cyan-400">github.com/eventschedule</div>
            <div class="pt-2 text-gray-500 dark:text-gray-400">$ composer install</div>
            <div class="text-emerald-700 dark:text-emerald-400">Done!</div>
            <div class="text-gray-600 dark:text-gray-400">$ php artisan serve</div>
            <div class="text-gray-600 dark:text-gray-400">Server running...</div>
        </div>
    </x-marketing.feature-banner>

    <!-- ============================================================ -->
    <!-- And everything else                                         -->
    <!-- ============================================================ -->
    @php
        $moreFeatures = [
            [
                'href' => route('marketing.integrations'),
                'aria' => 'Learn more about integrations',
                'title' => 'Integrations',
                'desc' => 'Connect Event Schedule to the calendar, payment and invoicing tools you already run on.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />',
            ],
            [
                'href' => route('marketing.white_label'),
                'aria' => 'Learn more about white-label branding',
                'title' => 'White Label',
                'desc' => 'Remove Event Schedule branding so the schedule your guests see is entirely yours.',
                'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
            ],
            [
                'href' => route('marketing.embed_calendar'),
                'aria' => 'Learn more about embedding your calendar',
                'title' => 'Embed Calendar',
                'desc' => 'Drop your schedule into any website with a single iframe. No plugin required.',
                'chip' => 'bg-sky-100 dark:bg-sky-500/20',
                'text' => 'text-sky-600 dark:text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />',
            ],
            [
                'href' => route('marketing.embed_tickets'),
                'aria' => 'Learn more about the embeddable ticket widget',
                'title' => 'Embed Tickets',
                'desc' => 'Sell tickets straight from your own site with an embeddable checkout widget.',
                'chip' => 'bg-cyan-100 dark:bg-cyan-500/20',
                'text' => 'text-cyan-600 dark:text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />',
            ],
            [
                'href' => route('marketing.availability'),
                'aria' => 'Learn more about availability management',
                'title' => 'Availability',
                'desc' => 'Everyone on a talent schedule marks the dates they cannot play, and the whole team sees the marks.',
                'chip' => 'bg-teal-100 dark:bg-teal-500/20',
                'text' => 'text-teal-600 dark:text-teal-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            ],
            [
                'href' => route('marketing.carpool'),
                'aria' => 'Learn more about carpool matching',
                'title' => 'Carpool Matching',
                'desc' => 'Let attendees offer and claim rides to your event, with driver approval and reviews.',
                'chip' => 'bg-amber-100 dark:bg-amber-500/20',
                'text' => 'text-amber-600 dark:text-amber-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0H21a.75.75 0 0 0 .75-.75v-3.375c0-.621-.504-1.125-1.125-1.125H2.25" />',
            ],
            [
                'href' => route('marketing.custom_css'),
                'aria' => 'Learn more about custom CSS',
                'title' => 'Custom CSS',
                'desc' => 'Take full control of your schedule styling with your own stylesheet.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
            ],
            [
                'href' => route('marketing.custom_labels'),
                'aria' => 'Learn more about custom labels',
                'title' => 'Custom Labels',
                'desc' => 'Rename the words your guests see so the schedule speaks your language.',
                'chip' => 'bg-sky-100 dark:bg-sky-500/20',
                'text' => 'text-sky-600 dark:text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6Z" />',
            ],
            [
                'href' => marketing_url('/docs/subscriptions#overview'),
                'aria' => 'Learn more about passes and subscriptions',
                'title' => 'Passes & Subscriptions',
                'desc' => 'Sell multi-visit passes, memberships, festival and season tickets with usage tracking.',
                'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />',
            ],
            [
                'href' => marketing_url('/docs/tickets#checkin-dashboard'),
                'aria' => 'Learn more about the check-in dashboard',
                'title' => 'Check-in Dashboard',
                'desc' => 'Scan tickets at the door and watch arrivals update live across every device.',
                'chip' => 'bg-teal-100 dark:bg-teal-500/20',
                'text' => 'text-teal-600 dark:text-teal-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM13.5 14.625h2.25v2.25H13.5v-2.25ZM18 18.75h2.25V21H18v-2.25Z" />',
            ],
            [
                'href' => marketing_url('/docs/developer/api#authentication'),
                'aria' => 'Learn more about the REST API and webhooks',
                'title' => 'API & Webhooks',
                'desc' => 'Full CRUD REST API plus webhooks, so Event Schedule fits into whatever you already built.',
                'chip' => 'bg-gray-100 dark:bg-gray-500/20',
                'text' => 'text-gray-600 dark:text-gray-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />',
            ],
            [
                'href' => marketing_url('/docs/account-settings#backup'),
                'aria' => 'Learn more about backup and restore',
                'title' => 'Backup & Restore',
                'desc' => 'Export everything you have created and bring it back whenever you need it.',
                'chip' => 'bg-cyan-100 dark:bg-cyan-500/20',
                'text' => 'text-cyan-600 dark:text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75" />',
            ],
        ];
    @endphp
    <section id="more" class="relative scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    And everything else you'd expect
                </h2>
            </div>
            {{-- Two columns even at 390px: twelve stacked paragraph cards added
                 ~1,500px of scroll for a secondary grid. On mobile these collapse
                 to icon + title, which is all this section needs to do. --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">
                @foreach ($moreFeatures as $feature)
                    <a href="{{ $feature['href'] }}" class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-3.5 transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40 sm:p-5" data-reveal aria-label="{{ $feature['aria'] }}">
                        <div class="mb-2.5 flex items-center gap-2.5 sm:gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $feature['chip'] }} sm:h-9 sm:w-9">
                                <svg class="h-4 w-4 {{ $feature['text'] }} sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $feature['icon'] !!}</svg>
                            </span>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">{{ $feature['title'] }}</h3>
                        </div>
                        <p class="hidden flex-grow text-sm text-gray-600 dark:text-gray-400 sm:block">{{ $feature['desc'] }}</p>
                        <span class="mt-auto hidden items-center gap-1 pt-3 text-sm font-medium text-blue-700 dark:text-blue-400 transition-all group-hover:gap-2 sm:inline-flex">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- The 12 cards above each have a page behind them. These do not, and were
         simply missing from the site's most complete page. Every row cites the
         gate it is really behind (docs/FEATURES.md), because a list like this
         goes stale the moment a tier moves. --}}
    @php
        $alsoIncluded = [
            ['Installment payments', 'Split a ticket over monthly charges, taken off the saved card', 'Pro'],
            ['Ticket add-ons', 'Parking, merchandise or a workshop, each with its own stock', 'Pro'],
            ['Promo codes', 'Percentage or fixed, with usage limits and an expiry date', 'Pro'],
            ['Ticket waitlist', 'Notify people automatically when a sold-out type frees up', 'Pro'],
            ['Multi-event cart', 'One checkout across several of your events, paid as a single amount', 'Free'],
            ['Bulk attendee import', 'Up to 5,000 rows from a CSV, for a list you already hold', 'Pro'],
            ['Eventbrite import', 'Bring an existing run of events across in one go', 'Pro'],
            ['Event templates', 'Save an event you repeat and start the next one from it', 'Pro'],
            ['Sales CSV export', 'Every sale across every schedule you own, custom fields included', 'Pro'],
            ['Push notifications', 'Browser and mobile web push mirroring your email alerts', 'Pro'],
            ['Agenda scanning', 'Photograph a running order and get the parts back as event parts', 'Enterprise'],
            ['WhatsApp event creation', 'Message or photograph an event and it lands on the schedule', 'Enterprise'],
            ['Event cloning', 'Duplicate any event as the starting point for the next one', 'Free'],
            ['iCal and RSS feeds', 'Every schedule publishes both, and every date downloads as .ics', 'Free'],
            ['Schedule transfer', 'Hand a schedule and its ticket revenue to another account', 'Free'],
            ['Sponsor and partner logos', 'A tiered logo wall on your schedule page, for the people funding it', 'Pro'],
        ];
        $alsoBadge = [
            'Free' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
            'Pro' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
            'Enterprise' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        ];
    @endphp
    <section id="also" class="relative scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-balance text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>
                    And the small print, which is mostly good news
                </h2>
                <p class="mt-3 text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                    Sixteen more things the app does, and the plan each one sits on.
                </p>
            </div>
            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="35">
                @foreach ($alsoIncluded as [$alsoName, $alsoDesc, $alsoTier])
                    <div class="border-gray-200 ltr:border-l ltr:pl-4 rtl:border-r rtl:pr-4 dark:border-white/10" data-reveal>
                        <dt class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alsoName }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $alsoBadge[$alsoTier] }}">{{ $alsoTier }}</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $alsoDesc }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-10 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>
                Selfhosted installs resolve to the top tier, so every row above is included.
                <a href="{{ marketing_url('/pricing') }}" class="font-semibold text-blue-600 underline decoration-blue-300 underline-offset-4 transition-colors hover:text-blue-500 dark:text-blue-400 dark:decoration-blue-500/50">See the full plan comparison</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    @php
        $faqs = [
            [
                'q' => 'Is Event Schedule really free?',
                'a' => 'Yes. Unlimited events, unlimited schedules, calendar sync, free registration with capacity limits, analytics and QR check-in at the door are all included on the free plan, with no time limit and no credit card required. Two things are metered rather than unlimited: newsletters reach 10 recipients a month, and you can sell up to 25 paid tickets a month.',
            ],
            [
                'q' => 'Do you take a cut of ticket sales?',
                'a' => 'No. Event Schedule charges zero platform fees on tickets, on every plan including free. You connect your own Stripe account and payouts go straight to you, so the only deduction is Stripe processing. The free plan caps volume at 25 paid tickets a month rather than taking a cut; Pro removes the cap.',
            ],
            [
                'q' => 'Can buyers choose their own seat?',
                'a' => 'Yes, on the Enterprise plan. Draw your room once as a reusable seating plan - levels, sections, rows, tables, standing areas and wheelchair spaces - attach it to an event, and buyers pick their seats off the map. One plan covers every date of a run, and a single date can be changed on its own. Your box office gets the same map to hold seats back, take a booking over the phone, move somebody or release one seat.',
            ],
            [
                'q' => 'Can I use my own domain?',
                'a' => 'Yes. Custom domains are available on the Enterprise plan. You add one CNAME record at your registrar, and the SSL certificate is issued automatically once it resolves.',
            ],
            [
                'q' => 'Can I run it on my own server?',
                'a' => 'Yes. Event Schedule is open source under the Attribution Assurance License. Selfhosted installs include every Enterprise feature at no cost, and the app updates itself with one click from the admin panel.',
            ],
            [
                'q' => 'Do I need a credit card to start?',
                'a' => 'No. You can create a schedule and start publishing events without entering any payment details. You only add a card if you choose to upgrade to Pro or Enterprise.',
            ],
        ];
    @endphp
    <x-seo.faq-schema :items="$faqs" />
    <section class="relative bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-balance mb-10 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                Common questions
            </h2>
            <div class="space-y-3" data-reveal-group="60">
                @foreach ($faqs as $faq)
                    <details name="faq" class="group rounded-2xl border border-gray-200 bg-white px-5 py-4 transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40" data-reveal>
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ $faq['q'] }}
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        Ready to <span class="text-gradient-features">get started?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free event schedule in seconds. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-500 dark:text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
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
            ['sell', 'Sell'],
            ['schedule', 'Schedule'],
            ['promote', 'Promote'],
            ['engage', 'Engage'],
            ['own-it', 'Make it yours'],
            ['more', 'Everything else'],
            ['also', 'Small print'],
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
