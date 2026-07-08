<x-marketing-layout>
    <x-slot name="title">FAQ | Event Schedule - Common Questions Answered</x-slot>
    <x-slot name="description">Find answers to frequently asked questions about Event Schedule. Learn about pricing, features, ticketing, Google Calendar sync, selfhosting, and more.</x-slot>
    <x-slot name="breadcrumbTitle">FAQ</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What is Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule is a free, open-source platform that lets you create professional, shareable event calendars and sell tickets. Whether you're a musician sharing gig dates, a venue managing your lineup, or a food truck posting daily locations, Event Schedule gives you a professional calendar your audience can easily access."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule really free?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited events, mobile-optimized calendars, Google Calendar sync, team collaboration, venue location maps, and more. These features are free forever, not a trial. The Pro plan (which adds ticketing, event boosting, and branding removal) comes with a 7-day free trial, then $5/month after that. Enterprise adds custom domains, private events, multiple team members, and AI features at $15/month."
            }
            },
            {
                "@type": "Question",
                "name": "Do I need technical skills to use it?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Creating your schedule takes just a few clicks. Add your events, customize the look, and share the link. You can also paste event details or drop an image and our AI will extract the information automatically."
                }
            },
            {
                "@type": "Question",
                "name": "Can I embed my schedule on my website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Every schedule has an embed code you can copy and paste into your website. The embedded calendar matches your site and updates automatically when you add or change events."
                }
            },
            {
                "@type": "Question",
                "name": "What's the difference between Free and Pro?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The Free plan includes everything you need for a professional event calendar: unlimited events, Google Calendar sync, team collaboration, and mobile-optimized design. Pro adds ticketing with QR check-ins, the ability to remove Event Schedule branding, event graphics generation, event boosting with ads, custom CSS styling, and REST API access. Enterprise adds custom domains, private and password-protected events, multiple team members, AI features, email scheduling, agenda scanning, and priority support."
                }
            },
            {
                "@type": "Question",
                "name": "Do you take a percentage of my ticket sales?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. We never take a cut of your ticket revenue. When you sell tickets through Event Schedule, you pay only Stripe's standard processing fees (approximately 2.9% + $0.30 per transaction). The rest is yours."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when my free trial ends?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "After your 7-day free trial, your card is automatically charged for Pro ($5/month or $50/year) or Enterprise ($15/month or $150/year). You can cancel anytime during or after the trial. If you cancel, your account reverts to the Free plan. You keep all your events and data; you just lose access to paid features like ticketing and branding removal."
                }
            },
            {
                "@type": "Question",
                "name": "Can I cancel anytime?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. There are no contracts or cancellation fees. Cancel your Pro subscription whenever you want, and your account stays active on the Free plan."
                }
            },
            {
                "@type": "Question",
                "name": "How do I start selling tickets?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "First, connect your Stripe account (takes about 2 minutes). Then, when creating or editing an event, add ticket types with names, prices, and quantities. Your attendees can purchase directly from your event page and receive tickets with QR codes via email."
                }
            },
            {
                "@type": "Question",
                "name": "Can I create different ticket types for one event?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can create multiple ticket types per event, such as General Admission, VIP, Early Bird, or Student pricing. Each type can have its own price, quantity limit, sales start date, sales end date, and description."
                }
            },
            {
                "@type": "Question",
                "name": "How do QR check-ins work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When someone buys a ticket, they receive an email with a QR code. At your event, open the check-in screen on any smartphone or tablet, scan the QR code, and the system verifies the ticket and marks it as used. No special hardware required."
                }
            },
            {
                "@type": "Question",
                "name": "Can I collect additional information from ticket buyers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can add custom fields to your ticket forms, including text fields, dropdowns, date pickers, and yes/no questions. Use these to collect meal preferences, t-shirt sizes, accessibility needs, or any other information."
                }
            },
            {
                "@type": "Question",
                "name": "Can I offer free tickets alongside paid ones?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can mix free and paid ticket types on the same event. This is useful for comp tickets, volunteer passes, or free admission with optional paid upgrades."
                }
            },
            {
                "@type": "Question",
                "name": "Does Event Schedule sync with Google Calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, and it's bidirectional. Events you create in Event Schedule automatically appear in your connected Google Calendar. Events you add to Google Calendar also sync back to Event Schedule. Changes update in real-time via webhooks."
                }
            },
            {
                "@type": "Question",
                "name": "Can my audience add events to their personal calendars?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Every event page has \"Add to Calendar\" buttons that let visitors save the event to Google Calendar, Apple Calendar, Outlook, or download an ICS file. This works for both you and your attendees."
                }
            },
            {
                "@type": "Question",
                "name": "What's the difference between a schedule, a sub-schedule, and an event?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "A schedule is your main calendar (like \"The Blue Note\" or \"DJ Sarah\"). Sub-schedules help organize events within that calendar by category, room, or series (like \"Main Stage\" and \"Lounge\"). Events are the individual shows, performances, or happenings on your schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I create recurring events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When creating an event, you can set it to repeat daily, weekly, biweekly, or monthly. You can also specify an end date or number of occurrences. Each occurrence can be edited individually if needed."
                }
            },
            {
                "@type": "Question",
                "name": "Can I send newsletters to my followers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Pro users can create and send branded newsletters to followers and ticket buyers using a drag-and-drop builder. Choose from pre-built templates, add event listings, images, and buttons, and track opens and clicks with built-in analytics."
                }
            },
            {
                "@type": "Question",
                "name": "Does Event Schedule have email marketing?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The built-in newsletter feature includes audience segmentation, A/B testing, scheduled sends, and open/click tracking. You can target all followers, ticket buyers, specific sub-schedules, or a manual list of email addresses."
                }
            },
            {
                "@type": "Question",
                "name": "Who can see my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "By default, your schedule is public so your audience can find it. However, you control what information appears. You can also make individual events private or require a password."
                }
            },
            {
                "@type": "Question",
                "name": "Is payment processing secure?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. We never see or store your customers' credit card numbers. All payment processing happens through Stripe, which is PCI-DSS compliant and uses industry-standard encryption."
                }
            },
            {
                "@type": "Question",
                "name": "Do you sell my data?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. We don't sell, share, or use your data for advertising. Our built-in analytics are privacy-first and don't use external trackers. If you selfhost, your data stays entirely on your own servers."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule open source?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is open source and licensed under the AAL (Attribution Assurance License). You can view the full source code on GitHub, contribute improvements, report issues, or fork it for your own projects."
                }
            },
            {
                "@type": "Question",
                "name": "Can I selfhost Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Download the code from GitHub and run it on your own server. Selfhosting is completely free and includes all features, including Pro features. This gives you complete control over your data and customization options."
                }
            },
            {
                "@type": "Question",
                "name": "Is there an API?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Pro users have access to our REST API, which lets you programmatically create events, manage schedules, retrieve ticket sales, and integrate with your own systems."
                }
            },
            {
                "@type": "Question",
                "name": "What languages does Event Schedule support?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The interface is available in 11 languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, and Russian. You can also use AI-powered translation to automatically translate your event descriptions."
                }
            },
            {
                "@type": "Question",
                "name": "Can I customize my schedule's appearance?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can customize colors, fonts, backgrounds, header images, profile images, and choose between grid or list layouts. Pro users and above can also add custom CSS for complete brand control. Enterprise users can use a custom domain."
                }
            },
            {
                "@type": "Question",
                "name": "Does Event Schedule support multiple languages?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The interface supports 11 languages, and each schedule can have its own language setting. You can also use AI-powered translation to automatically translate event descriptions into other languages."
                }
            },
            {
                "@type": "Question",
                "name": "Can I track who views my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Built-in analytics track page views, unique visitors, devices, browsers, and traffic sources. Analytics is a Pro feature and uses privacy-first tracking with no external trackers."
                }
            },
            {
                "@type": "Question",
                "name": "Does Event Schedule integrate with Google Analytics?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule has its own built-in analytics dashboard with page views, unique visitors, device breakdowns, and traffic sources. No external analytics integration is needed."
                }
            },
            {
                "@type": "Question",
                "name": "Can I import events from a flyer or image?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The AI-powered import feature can extract event details from images, flyers, and pasted text. Simply drop an image or paste event information and the AI will parse it into structured event data."
                }
            },
            {
                "@type": "Question",
                "name": "Can others submit events to my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable 'Accept Event Requests' in your schedule settings to let others submit events. You can optionally require approval before submitted events appear on your schedule."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when tickets sell out?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When all tickets sell out, a waitlist button appears automatically. Fans can join the waitlist by entering their name and email. When spots open up (from cancellations or refunds), the next person in line is notified via email and given 24 hours to complete their purchase."
                }
            },
            {
                "@type": "Question",
                "name": "Can I track check-ins in real time?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The live check-in dashboard shows real-time progress bars, per-ticket-type breakdowns, and a recent activity feed with attendee names and check-in times. It auto-refreshes every 10 seconds and works on any device."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-faq {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-faq {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-faq {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature motif: a row of question marks (the help center) */
        .es-q {
            flex: 0 0 auto;
            font-weight: 800;
            line-height: 1;
            color: rgba(37, 99, 235, 0.8);
            animation: es-q-pulse var(--q-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--q-delay, 0s);
        }
        @keyframes es-q-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-q, .animate-pulse-slow { animation: none !important; }
            .es-q { opacity: 0.55; transform: none; }
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
    <section class="es-hero relative flex min-h-[calc(60svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <div class="es-qs absolute bottom-6 left-0 right-0 mx-auto hidden h-14 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [22, 30, 18, 26][$i % 4]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-q" style="font-size: {{ $sz }}px; --q-dur: {{ $dur }}s; --q-delay: {{ $delay }}s;">?</span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Help Center</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Frequently Asked</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-faq">Questions</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Everything you need to know about Event Schedule. Can't find what you're looking for? <a href="mailto:{{ config('app.support_email') }}" class="text-blue-500 transition-colors hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300">Contact us</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. FAQ Content                                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            <!-- Getting Started -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Getting Started</h2>
                </div>
                <div class="space-y-4" data-reveal-group="60">
                    <details name="faq-getting-started" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What is Event Schedule?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Event Schedule is a free, open-source platform that lets you create professional, shareable event calendars and sell tickets. Whether you're a musician sharing gig dates, a venue managing your lineup, or a food truck posting daily locations, Event Schedule gives you a professional calendar your audience can easily access.</p></div>
                    </details>
                    <details name="faq-getting-started" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Is Event Schedule really free?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. The free plan includes unlimited events, mobile-optimized calendars, Google Calendar sync, team collaboration, venue location maps, and more. These features are free forever, not a trial. The <a href="{{ marketing_url('/pricing') }}" class="text-blue-600 underline hover:text-blue-700">Pro plan</a> (which adds ticketing, event boosting, and branding removal) comes with a 7-day free trial, then $5/month after that. Enterprise adds custom domains, private events, multiple team members, and AI features at $15/month.</p></div>
                    </details>
                    <details name="faq-getting-started" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Do I need technical skills to use it?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">No. Creating your schedule takes just a few clicks. Add your events, customize the look, and share the link. You can also paste event details or drop an image and our <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 underline hover:text-blue-700">AI will extract the information</a> automatically.</p></div>
                    </details>
                    <details name="faq-getting-started" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I embed my schedule on my website?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Every schedule has an <a href="{{ marketing_url('/docs/sharing') }}" class="text-blue-600 underline hover:text-blue-700">embed code</a> you can copy and paste into your website. The embedded calendar matches your site and updates automatically when you add or change events.</p></div>
                    </details>
                </div>
            </div>

            <!-- Pricing & Billing -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 shadow-lg shadow-blue-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pricing & Billing</h2>
                </div>
                <div class="space-y-4" data-reveal-group="60">
                    <details name="faq-pricing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What's the difference between Free and Pro?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">The Free plan includes everything you need for a professional event calendar: unlimited events, Google Calendar sync, team collaboration, and mobile-optimized design. Pro adds ticketing with QR check-ins, the ability to remove Event Schedule branding, event graphics generation, event boosting with ads, custom CSS styling, and REST API access. Enterprise adds custom domains, private and password-protected events, multiple team members, AI features, email scheduling, agenda scanning, and priority support. <a href="{{ marketing_url('/pricing') }}" class="text-blue-600 underline hover:text-blue-700">See our pricing page</a> for details.</p></div>
                    </details>
                    <details name="faq-pricing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Do you take a percentage of my ticket sales?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">No. We never take a cut of your ticket revenue. When you sell tickets through Event Schedule, you pay only <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 underline hover:text-blue-700">Stripe's</a> standard processing fees (approximately 2.9% + $0.30 per transaction). The rest is yours.</p></div>
                    </details>
                    <details name="faq-pricing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What happens when my free trial ends?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">After your 7-day free trial, your card is automatically charged for Pro ($5/month or $50/year) or Enterprise ($15/month or $150/year). You can cancel anytime during or after the trial. If you cancel, your account reverts to the Free plan. You keep all your events and data; you just lose access to paid features like ticketing and branding removal.</p></div>
                    </details>
                    <details name="faq-pricing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I cancel anytime?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. There are no contracts or cancellation fees. Cancel your Pro subscription whenever you want, and your account stays active on the Free plan.</p></div>
                    </details>
                </div>
            </div>

            <!-- Ticketing & Payments -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 shadow-lg shadow-sky-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Ticketing & Payments</h2>
                </div>
                <div class="space-y-4" data-reveal-group="50">
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">How do I start selling tickets?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">First, <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 underline hover:text-blue-700">connect your Stripe account</a> (takes about 2 minutes). Then, when creating or editing an event, add ticket types with names, prices, and quantities. Your attendees can purchase directly from your event page and receive tickets with QR codes via email. Learn more about <a href="{{ marketing_url('/features/ticketing') }}" class="text-blue-600 underline hover:text-blue-700">ticketing</a>.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I create different ticket types for one event?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. You can create multiple ticket types per event, such as General Admission, VIP, Early Bird, or Student pricing. Each type can have its own price, quantity limit, sales start date, sales end date, and description.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">How do QR check-ins work?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">When someone buys a ticket, they receive an email with a QR code. At your event, open the check-in screen on any smartphone or tablet, scan the QR code, and the system verifies the ticket and marks it as used. No special hardware required.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I collect additional information from ticket buyers?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. You can add <a href="{{ marketing_url('/features/custom-fields') }}" class="text-blue-600 underline hover:text-blue-700">custom fields</a> to your ticket forms, including text fields, dropdowns, date pickers, and yes/no questions. Use these to collect meal preferences, t-shirt sizes, accessibility needs, or any other information.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I offer free tickets alongside paid ones?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. You can mix free and paid ticket types on the same event. This is useful for comp tickets, volunteer passes, or free admission with optional paid upgrades.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What happens when tickets sell out?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">When all tickets sell out, a waitlist button appears automatically. Fans can join the waitlist by entering their name and email. When spots open up (from cancellations or refunds), the next person in line is notified via email and given 24 hours to complete their purchase. Learn more in the <a href="{{ marketing_url('/docs/tickets#waitlist') }}" class="text-blue-600 underline hover:text-blue-700">ticketing guide</a>.</p></div>
                    </details>
                    <details name="faq-ticketing" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I track check-ins in real time?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. The live <a href="{{ marketing_url('/docs/tickets#checkin-dashboard') }}" class="text-blue-600 underline hover:text-blue-700">check-in dashboard</a> shows real-time progress bars, per-ticket-type breakdowns, and a recent activity feed with attendee names and check-in times. It auto-refreshes every 10 seconds and works on any device.</p></div>
                    </details>
                </div>
            </div>

            <!-- Calendar & Sharing -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 shadow-lg shadow-blue-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Calendar & Sharing</h2>
                </div>
                <div class="space-y-4" data-reveal-group="60">
                    <details name="faq-calendar" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule sync with Google Calendar?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes, and it's bidirectional. Events you create in Event Schedule automatically appear in your connected <a href="{{ marketing_url('/google-calendar') }}" class="text-blue-600 underline hover:text-blue-700">Google Calendar</a>. Events you add to Google Calendar also sync back to Event Schedule. Changes update in real-time via webhooks.</p></div>
                    </details>
                    <details name="faq-calendar" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can my audience add events to their personal calendars?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Every event page has "Add to Calendar" buttons that let visitors save the event to Google Calendar, Apple Calendar, Outlook, or download an ICS file. This works for both you and your attendees.</p></div>
                    </details>
                    <details name="faq-calendar" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What's the difference between a schedule, a sub-schedule, and an event?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">A schedule is your main calendar (like "The Blue Note" or "DJ Sarah"). <a href="{{ marketing_url('/features/sub-schedules') }}" class="text-blue-600 underline hover:text-blue-700">Sub-schedules</a> help organize events within that calendar by category, room, or series (like "Main Stage" and "Lounge"). Events are the individual shows, performances, or happenings on your schedule.</p></div>
                    </details>
                    <details name="faq-calendar" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I create recurring events?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. When creating an event, you can set it to repeat daily, weekly, biweekly, or monthly. You can also specify an end date or number of occurrences. Each occurrence can be edited individually if needed.</p></div>
                    </details>
                </div>
            </div>

            <!-- Newsletters & Email -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 shadow-lg shadow-cyan-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Newsletters & Email</h2>
                </div>
                <div class="space-y-4" data-reveal-group="70">
                    <details name="faq-newsletters" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I send newsletters to my followers?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Pro users can create and send branded <a href="{{ marketing_url('/docs/newsletters') }}" class="text-blue-600 underline hover:text-blue-700">newsletters</a> to followers and ticket buyers using a drag-and-drop builder. Choose from pre-built templates, add event listings, images, and buttons, and track opens and clicks with built-in analytics.</p></div>
                    </details>
                    <details name="faq-newsletters" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule have email marketing?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. The built-in <a href="{{ marketing_url('/docs/newsletters') }}" class="text-blue-600 underline hover:text-blue-700">newsletter feature</a> includes audience segmentation, A/B testing, scheduled sends, and open/click tracking. You can target all followers, ticket buyers, specific sub-schedules, or a manual list of email addresses.</p></div>
                    </details>
                </div>
            </div>

            <!-- Customization -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Customization</h2>
                </div>
                <div class="space-y-4" data-reveal-group="70">
                    <details name="faq-customization" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I customize my schedule's appearance?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. You can customize colors, fonts, backgrounds, header images, profile images, and choose between grid or list layouts. Pro users and above can also add custom CSS for complete brand control. Enterprise users can use a custom domain. See our <a href="{{ marketing_url('/docs/schedule-styling') }}" class="text-blue-600 underline hover:text-blue-700">schedule styling guide</a> for details.</p></div>
                    </details>
                    <details name="faq-customization" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule support multiple languages?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. The interface supports 11 languages, and each schedule can have its own language setting. You can also use <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 underline hover:text-blue-700">AI-powered translation</a> to automatically translate event descriptions into other languages.</p></div>
                    </details>
                </div>
            </div>

            <!-- Analytics -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 shadow-lg shadow-teal-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics</h2>
                </div>
                <div class="space-y-4" data-reveal-group="70">
                    <details name="faq-analytics" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I track who views my schedule?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Built-in analytics track page views, unique visitors, devices, browsers, and traffic sources. Analytics is a Pro feature and uses privacy-first tracking with no external trackers. See our <a href="{{ marketing_url('/docs/analytics') }}" class="text-blue-600 underline hover:text-blue-700">analytics documentation</a> for details.</p></div>
                    </details>
                    <details name="faq-analytics" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule integrate with Google Analytics?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Event Schedule has its own built-in <a href="{{ marketing_url('/docs/analytics') }}" class="text-blue-600 underline hover:text-blue-700">analytics dashboard</a> with page views, unique visitors, device breakdowns, and traffic sources. No external analytics integration is needed.</p></div>
                    </details>
                </div>
            </div>

            <!-- Event Management -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 shadow-lg shadow-cyan-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Event Management</h2>
                </div>
                <div class="space-y-4" data-reveal-group="70">
                    <details name="faq-events" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I import events from a flyer or image?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. The AI-powered import feature can extract event details from images, flyers, and pasted text. Simply drop an image or paste event information and the AI will parse it into structured event data. Learn more in our <a href="{{ marketing_url('/docs/creating-events') }}" class="text-blue-600 underline hover:text-blue-700">creating events guide</a>.</p></div>
                    </details>
                    <details name="faq-events" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can others submit events to my schedule?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Enable "Accept Event Requests" in your schedule settings to let others submit events. You can optionally require approval before submitted events appear on your schedule. See <a href="{{ marketing_url('/docs/creating-schedules#engagement-requests') }}" class="text-blue-600 underline hover:text-blue-700">creating schedules</a> for setup details.</p></div>
                    </details>
                </div>
            </div>

            <!-- Privacy & Security -->
            <div class="mb-16">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Privacy & Security</h2>
                </div>
                <div class="space-y-4" data-reveal-group="70">
                    <details name="faq-privacy" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Who can see my schedule?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">By default, your schedule is public so your audience can find it. However, you control what information appears. You can also make individual events private or require a password.</p></div>
                    </details>
                    <details name="faq-privacy" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Is payment processing secure?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. We never see or store your customers' credit card numbers. All payment processing happens through <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 underline hover:text-blue-700">Stripe</a>, which is PCI-DSS compliant and uses industry-standard encryption.</p></div>
                    </details>
                    <details name="faq-privacy" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Do you sell my data?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">No. We don't sell, share, or use your data for advertising. Our built-in analytics are privacy-first and don't use external trackers. If you selfhost, your data stays entirely on your own servers.</p></div>
                    </details>
                </div>
            </div>

            <!-- Technical & Selfhosting -->
            <div>
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 shadow-lg shadow-gray-700/25">
                        <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Technical & Selfhosting</h2>
                </div>
                <div class="space-y-4" data-reveal-group="60">
                    <details name="faq-technical" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Is Event Schedule open source?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Event Schedule is <a href="{{ marketing_url('/open-source') }}" class="text-blue-600 underline hover:text-blue-700">open source</a> and licensed under the AAL (Attribution Assurance License). You can view the full source code on <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700">GitHub</a>, contribute improvements, report issues, or fork it for your own projects.</p></div>
                    </details>
                    <details name="faq-technical" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I selfhost Event Schedule?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Download the code from <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700">GitHub</a> and run it on your own server. Selfhosting is completely free and includes all features, including Pro features. This gives you complete control over your data and customization options. <a href="{{ marketing_url('/selfhost') }}" class="text-blue-600 underline hover:text-blue-700">Learn more about selfhosting</a>.</p></div>
                    </details>
                    <details name="faq-technical" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">Is there an API?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">Yes. Pro users have access to our <a href="{{ marketing_url('/docs/developer/api') }}" class="text-blue-600 underline hover:text-blue-700">REST API</a>, which lets you programmatically create events, manage schedules, retrieve ticket sales, and integrate with your own systems.</p></div>
                    </details>
                    <details name="faq-technical" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                            <span class="font-semibold text-gray-900 dark:text-white">What languages does Event Schedule support?</span>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="px-6 pb-5"><p class="faq-answer leading-relaxed text-gray-600 dark:text-gray-300">The interface is available in 11 languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, and Russian. You can also use <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 underline hover:text-blue-700">AI-powered translation</a> to automatically translate your event descriptions.</p></div>
                    </details>
                </div>
            </div>

        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Finale                                                   -->
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
                        Still have <span class="text-gradient-faq">questions?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        We're here to help. Browse our <a href="{{ route('marketing.docs') }}" class="text-white underline hover:text-white/90">documentation</a> for detailed guides, or reach out and we'll get back to you as soon as possible.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="mailto:{{ config('app.support_email') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-8 py-4 text-lg font-semibold text-blue-600 shadow-xl transition-all hover:scale-105">
                            Contact Us
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/30 bg-white/20 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/30">
                            Get Started Free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
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
