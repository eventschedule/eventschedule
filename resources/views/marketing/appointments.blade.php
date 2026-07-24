<x-marketing-layout>
    @php
        $faqs = [
            [
                'q' => 'How does appointment booking work?',
                'a' => 'Create an appointment type with a duration and the weekly hours you take bookings for. Guests open your booking page, pick an open time, and book it. You can auto-confirm bookings or approve each request yourself.',
            ],
            [
                'q' => 'Can I charge for appointments?',
                'a' => 'Yes. Appointment types can be free or paid via Stripe, a payment link, or cash. Free bookings confirm instantly, and paid types are only shown to guests once a working payment method is connected.',
            ],
            [
                'q' => 'Does it avoid double-booking?',
                'a' => 'Yes. Open times respect your weekly hours, buffers, and minimum notice, and they block against the events already on your schedule and your connected Google, Outlook, or CalDAV calendar.',
            ],
            [
                'q' => 'Can I approve bookings before they are confirmed?',
                'a' => 'Turn on approval for an appointment type and new bookings arrive as requests. Guests are told nothing is booked until you confirm, and they are emailed either way when you accept or decline.',
            ],
            [
                'q' => 'What do guests receive after booking?',
                'a' => 'A confirmation email with a calendar invite and a link to manage or cancel the booking, plus a reminder email before the appointment. Guests always see times in their own timezone.',
            ],
            [
                'q' => 'Which plan includes appointment booking?',
                'a' => 'Appointment booking is a Pro feature on the hosted platform and is included on all selfhosted deployments. Existing bookings keep working if your plan changes.',
            ],
        ];
    @endphp

    <x-slot name="title">Appointment Booking - Event Schedule</x-slot>
    <x-slot name="description">Calendly-style appointment booking built into your schedule. Set weekly hours and buffers, take free or paid bookings, and never get double-booked against your synced calendars.</x-slot>
    <x-slot name="breadcrumbTitle">Appointments</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Appointments",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Appointment booking built into your schedule. Set weekly hours and buffers, take free or paid bookings, and never get double-booked against your synced calendars.",
        "featureList": [
            "Bookable appointment types with duration and weekly hours",
            "Buffers and minimum notice between bookings",
            "Free or paid bookings via Stripe, a payment link, or cash",
            "No double-booking against synced calendars",
            "Approval or instant confirmation",
            "Confirmation emails, reminders, and timezone-aware slots"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <x-seo.faq-schema :items="$faqs" />
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (brand blue to sky to cyan) */
        .text-gradient-appointments {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-appointments {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-appointments {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature motif: a row of clocks pulsing along the bottom edge */
        .es-appt-icon {
            flex: 0 0 auto;
            color: rgba(14, 165, 233, 0.8);
            animation: es-appt-pulse var(--ap-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--ap-delay, 0s);
        }
        @keyframes es-appt-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(14, 165, 233, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-appt-icon, .animate-pulse-slow, .animate-pulse { animation: none !important; }
            .es-appt-icon { opacity: 0.55; transform: none; }
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
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Clock motif along the bottom edge -->
            <div class="absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-appt-icon" style="--ap-dur: {{ $dur }}s; --ap-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Appointments</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Appointment booking,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-appointments">built in</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Create an appointment type with your weekly hours, share your booking link, and guests grab an open time. Free or paid, approval optional, never double-booked.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#0a0a0f]">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#how-it-works" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] focus-visible:ring-offset-2 dark:text-white dark:focus-visible:ring-offset-[#0a0a0f]">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Weekly hours -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Weekly hours
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Hours that fit your week</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set the hours you take bookings for each day, add buffers between appointments, and control how far ahead guests can book.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Monday</span>
                                        <span class="text-sky-600 dark:text-sky-400">9:00 AM - 5:00 PM</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Tuesday</span>
                                        <span class="text-sky-600 dark:text-sky-400">9:00 AM - 5:00 PM</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Wednesday</span>
                                        <span class="text-gray-400 dark:text-gray-500">Unavailable</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Thursday</span>
                                        <span class="text-sky-600 dark:text-sky-400">10:00 AM - 6:00 PM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One link -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            One link
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Guests pick a time</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Share your booking link anywhere. Guests see your open slots in their own timezone and grab the one that works.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span class="rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-center text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">9:00 AM</span>
                                    <span class="rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-center text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">11:30 AM</span>
                                    <span class="rounded-lg bg-gradient-to-r from-[#4E81FA] to-[#0EA5E9] px-2 py-1.5 text-center text-xs font-bold text-white shadow-sm">3:00 PM</span>
                                    <span class="rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-center text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">4:30 PM</span>
                                </div>
                                <div class="mt-2 border-t border-gray-100 pt-1.5 text-center text-[10px] font-medium text-gray-400 dark:border-white/5">9:00 AM PST · 7:00 PM CET</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Free or paid -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Free or paid
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Charge for your time</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Offer free intro calls or paid sessions. Take payment with Stripe, a payment link, or cash, and free bookings confirm instantly.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            <span class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">Free</span>
                            <span class="rounded-lg border border-emerald-400/30 bg-emerald-500/20 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">$50 / session</span>
                            <span class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">Stripe</span>
                            <span class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">Cash</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Never double-booked (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Calendar-aware
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Never double-booked</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Open slots block against the events already on your schedule and your connected Google, Outlook, or CalDAV calendar. New bookings sync back the other way.</p>
                            </div>
                            <div class="relative px-2" aria-hidden="true">
                                <div class="flex items-center justify-between gap-6">
                                    <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                        <div class="mb-2 text-[10px] font-bold uppercase tracking-wide text-gray-400">Your schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="rounded-lg bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">Consult · 3:00 PM</div>
                                            <div class="rounded-lg bg-gray-200 px-2 py-1 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">Rehearsal · 6:00 PM</div>
                                        </div>
                                    </div>
                                    <div class="relative h-1 w-14 shrink-0 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE] opacity-40">
                                        <div class="es-sync-dot"></div>
                                    </div>
                                    <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                        <div class="mb-2 text-[10px] font-bold uppercase tracking-wide text-gray-400">Google Calendar</div>
                                        <div class="space-y-1.5">
                                            <div class="rounded-lg bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">Consult · 3:00 PM</div>
                                            <div class="rounded-lg bg-gray-200 px-2 py-1 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">Dentist · 10:00 AM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Approve or auto-confirm -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Your rules
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Approve or auto-confirm</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Confirm bookings automatically, or require approval and accept each request from your Appointments tab. Guests are emailed either way.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Jamie · Intro call</span>
                                <span class="text-xs text-gray-400">Tue 3:00 PM</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="flex-1 rounded-lg bg-emerald-500/20 px-2 py-1.5 text-center text-xs font-semibold text-emerald-700 dark:text-emerald-300">Accept</span>
                                <span class="flex-1 rounded-lg bg-gray-200 px-2 py-1.5 text-center text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">Decline</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. How it works                                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-appointments">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">From setup to a full calendar in four steps.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4" data-reveal-group="90">
                @php
                    $steps = [
                        ['Create an appointment type', 'Name it, pick a duration, and choose free or paid. A 30 minute intro call is ready out of the box.'],
                        ['Set your weekly hours', 'Choose the hours you take bookings for each day, with buffers, minimum notice, and a booking window.'],
                        ['Share your booking link', 'Put your /book link in your bio, your email signature, or right on your schedule page.'],
                        ['Guests pick a time', 'They book an open slot in their own timezone and get a confirmation with a calendar invite. You get notified.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 text-2xl font-bold text-white shadow-lg shadow-cyan-500/25">
                            {{ $si + 1 }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $step[0] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Good to know                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Good to <span class="text-gradient-appointments">know</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Small details that make bookings easy to run.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $facts = [
                        ['Calendar invites', 'Confirmations include an invite guests can add in one tap', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['Automatic reminders', 'Guests get a reminder email before the appointment', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                        ['Timezone-aware', 'Guests always see open times in their own timezone', 'M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Guests can cancel', 'Every confirmation includes a manage and cancel link', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Buffers and notice', 'Padding between bookings and a minimum lead time', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Multiple types', 'A free intro call and a paid session can live side by side', 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                    ];
                @endphp
                @foreach ($facts as $fact)
                    <div data-reveal class="rounded-2xl border border-cyan-200 bg-gradient-to-br from-blue-50 to-cyan-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 dark:border-cyan-500/20 dark:from-blue-900/30 dark:to-cyan-900/30">
                        <svg aria-hidden="true" class="mx-auto mb-3 h-8 w-8 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fact[2] }}" />
                        </svg>
                        <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">{{ $fact[0] }}</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-400">{{ $fact[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mx-auto mt-10 max-w-3xl rounded-2xl border border-cyan-200 bg-cyan-50 p-5 text-center dark:border-cyan-500/20 dark:bg-cyan-500/10" data-reveal>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Appointment booking is a Pro feature.</span>
                    On selfhosted deployments it is included on every schedule, no plan required.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Related Features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-8 pb-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card name="Availability" description="Track which days your team members are unavailable." :url="route('marketing.availability')" iconColor="sky">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook, and CalDAV calendars." :url="marketing_url('/features/calendar-sync')" iconColor="blue">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets to your events with zero platform fees." :url="marketing_url('/features/ticketing')" iconColor="emerald">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                Want the details? <x-link href="{{ route('marketing.docs.appointments') }}">Read the Appointments guide</x-link>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-appointments">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about appointment booking.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 9; $i++)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-appt-icon" style="--ap-dur: {{ $dur }}s; --ap-delay: {{ $delay }}s;">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start taking <span class="text-gradient-appointments">bookings</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your first appointment type in minutes and share one link.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
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

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
