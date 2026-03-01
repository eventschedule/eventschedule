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
                    "text": "Yes. You can create multiple ticket types per event, such as General Admission, VIP, Early Bird, or Student pricing. Each type can have its own price, quantity limit, and description."
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

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Help Center</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Frequently Asked<br>
                <span class="text-gradient">Questions</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto animate-reveal delay-200" style="opacity: 0;">
                Everything you need to know about Event Schedule. Can't find what you're looking for? <a href="mailto:contact@eventschedule.com" class="text-blue-400 hover:text-blue-300 transition-colors">Contact us</a>.
            </p>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- FAQ Content -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Getting Started -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Getting Started</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What is Event Schedule?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Event Schedule is a free, open-source platform that lets you create professional, shareable event calendars and sell tickets. Whether you're a musician sharing gig dates, a venue managing your lineup, or a food truck posting daily locations, Event Schedule gives you a professional calendar your audience can easily access.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Is Event Schedule really free?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. The free plan includes unlimited events, mobile-optimized calendars, Google Calendar sync, team collaboration, venue location maps, and more. These features are free forever, not a trial. The <a href="{{ marketing_url('/pricing') }}" class="text-blue-600 hover:text-blue-700 underline">Pro plan</a> (which adds ticketing, event boosting, and branding removal) comes with a 7-day free trial, then $5/month after that. Enterprise adds custom domains, private events, multiple team members, and AI features at $15/month.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Do I need technical skills to use it?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">No. Creating your schedule takes just a few clicks. Add your events, customize the look, and share the link. You can also paste event details or drop an image and our <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 hover:text-blue-700 underline">AI will extract the information</a> automatically.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I embed my schedule on my website?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Every schedule has an <a href="{{ marketing_url('/docs/sharing') }}" class="text-blue-600 hover:text-blue-700 underline">embed code</a> you can copy and paste into your website. The embedded calendar matches your site and updates automatically when you add or change events.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Billing -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pricing & Billing</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What's the difference between Free and Pro?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">The Free plan includes everything you need for a professional event calendar: unlimited events, Google Calendar sync, team collaboration, and mobile-optimized design. Pro adds ticketing with QR check-ins, the ability to remove Event Schedule branding, event graphics generation, event boosting with ads, custom CSS styling, and REST API access. Enterprise adds custom domains, private and password-protected events, multiple team members, AI features, email scheduling, agenda scanning, and priority support. <a href="{{ marketing_url('/pricing') }}" class="text-blue-600 hover:text-blue-700 underline">See our pricing page</a> for details.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Do you take a percentage of my ticket sales?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">No. We never take a cut of your ticket revenue. When you sell tickets through Event Schedule, you pay only <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 hover:text-blue-700 underline">Stripe's</a> standard processing fees (approximately 2.9% + $0.30 per transaction). The rest is yours.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What happens when my free trial ends?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">After your 7-day free trial, your card is automatically charged for Pro ($5/month or $50/year) or Enterprise ($15/month or $150/year). You can cancel anytime during or after the trial. If you cancel, your account reverts to the Free plan. You keep all your events and data; you just lose access to paid features like ticketing and branding removal.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I cancel anytime?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. There are no contracts or cancellation fees. Cancel your Pro subscription whenever you want, and your account stays active on the Free plan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticketing & Payments -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-sky-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Ticketing & Payments</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">How do I start selling tickets?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">First, <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 hover:text-blue-700 underline">connect your Stripe account</a> (takes about 2 minutes). Then, when creating or editing an event, add ticket types with names, prices, and quantities. Your attendees can purchase directly from your event page and receive tickets with QR codes via email. Learn more about <a href="{{ marketing_url('/features/ticketing') }}" class="text-blue-600 hover:text-blue-700 underline">ticketing</a>.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I create different ticket types for one event?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. You can create multiple ticket types per event, such as General Admission, VIP, Early Bird, or Student pricing. Each type can have its own price, quantity limit, and description.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">How do QR check-ins work?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">When someone buys a ticket, they receive an email with a QR code. At your event, open the check-in screen on any smartphone or tablet, scan the QR code, and the system verifies the ticket and marks it as used. No special hardware required.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I collect additional information from ticket buyers?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. You can add <a href="{{ marketing_url('/features/custom-fields') }}" class="text-blue-600 hover:text-blue-700 underline">custom fields</a> to your ticket forms, including text fields, dropdowns, date pickers, and yes/no questions. Use these to collect meal preferences, t-shirt sizes, accessibility needs, or any other information.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I offer free tickets alongside paid ones?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. You can mix free and paid ticket types on the same event. This is useful for comp tickets, volunteer passes, or free admission with optional paid upgrades.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What happens when tickets sell out?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">When all tickets sell out, a waitlist button appears automatically. Fans can join the waitlist by entering their name and email. When spots open up (from cancellations or refunds), the next person in line is notified via email and given 24 hours to complete their purchase. Learn more in the <a href="{{ marketing_url('/docs/tickets#waitlist') }}" class="text-blue-600 hover:text-blue-700 underline">ticketing guide</a>.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I track check-ins in real time?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. The live <a href="{{ marketing_url('/docs/tickets#checkin-dashboard') }}" class="text-blue-600 hover:text-blue-700 underline">check-in dashboard</a> shows real-time progress bars, per-ticket-type breakdowns, and a recent activity feed with attendee names and check-in times. It auto-refreshes every 10 seconds and works on any device.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar & Sharing -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Calendar & Sharing</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule sync with Google Calendar?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes, and it's bidirectional. Events you create in Event Schedule automatically appear in your connected <a href="{{ marketing_url('/google-calendar') }}" class="text-blue-600 hover:text-blue-700 underline">Google Calendar</a>. Events you add to Google Calendar also sync back to Event Schedule. Changes update in real-time via webhooks.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can my audience add events to their personal calendars?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Every event page has "Add to Calendar" buttons that let visitors save the event to Google Calendar, Apple Calendar, Outlook, or download an ICS file. This works for both you and your attendees.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What's the difference between a schedule, a sub-schedule, and an event?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">A schedule is your main calendar (like "The Blue Note" or "DJ Sarah"). <a href="{{ marketing_url('/features/sub-schedules') }}" class="text-blue-600 hover:text-blue-700 underline">Sub-schedules</a> help organize events within that calendar by category, room, or series (like "Main Stage" and "Lounge"). Events are the individual shows, performances, or happenings on your schedule.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I create recurring events?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. When creating an event, you can set it to repeat daily, weekly, biweekly, or monthly. You can also specify an end date or number of occurrences. Each occurrence can be edited individually if needed.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletters & Email -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center shadow-lg shadow-rose-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Newsletters & Email</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I send newsletters to my followers?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Pro users can create and send branded <a href="{{ marketing_url('/docs/newsletters') }}" class="text-blue-600 hover:text-blue-700 underline">newsletters</a> to followers and ticket buyers using a drag-and-drop builder. Choose from pre-built templates, add event listings, images, and buttons, and track opens and clicks with built-in analytics.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule have email marketing?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. The built-in <a href="{{ marketing_url('/docs/newsletters') }}" class="text-blue-600 hover:text-blue-700 underline">newsletter feature</a> includes audience segmentation, A/B testing, scheduled sends, and open/click tracking. You can target all followers, ticket buyers, specific sub-schedules, or a manual list of email addresses.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customization -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Customization</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I customize my schedule's appearance?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. You can customize colors, fonts, backgrounds, header images, profile images, and choose between grid or list layouts. Pro users and above can also add custom CSS for complete brand control. Enterprise users can use a custom domain. See our <a href="{{ marketing_url('/docs/schedule-styling') }}" class="text-blue-600 hover:text-blue-700 underline">schedule styling guide</a> for details.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule support multiple languages?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. The interface supports 11 languages, and each schedule can have its own language setting. You can also use <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 hover:text-blue-700 underline">AI-powered translation</a> to automatically translate event descriptions into other languages.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-teal-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I track who views my schedule?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Built-in analytics track page views, unique visitors, devices, browsers, and traffic sources. Analytics is a Pro feature and uses privacy-first tracking with no external trackers. See our <a href="{{ marketing_url('/docs/analytics') }}" class="text-blue-600 hover:text-blue-700 underline">analytics documentation</a> for details.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Does Event Schedule integrate with Google Analytics?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Event Schedule has its own built-in <a href="{{ marketing_url('/docs/analytics') }}" class="text-blue-600 hover:text-blue-700 underline">analytics dashboard</a> with page views, unique visitors, device breakdowns, and traffic sources. No external analytics integration is needed.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Management -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Event Management</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I import events from a flyer or image?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. The AI-powered import feature can extract event details from images, flyers, and pasted text. Simply drop an image or paste event information and the AI will parse it into structured event data. Learn more in our <a href="{{ marketing_url('/docs/creating-events') }}" class="text-blue-600 hover:text-blue-700 underline">creating events guide</a>.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can others submit events to my schedule?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Enable "Accept Event Requests" in your schedule settings to let others submit events. You can optionally require approval before submitted events appear on your schedule. See <a href="{{ marketing_url('/docs/creating-schedules#settings-requests') }}" class="text-blue-600 hover:text-blue-700 underline">creating schedules</a> for setup details.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy & Security -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Privacy & Security</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Who can see my schedule?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">By default, your schedule is public so your audience can find it. However, you control what information appears. You can also make individual events private or require a password.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Is payment processing secure?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. We never see or store your customers' credit card numbers. All payment processing happens through <a href="{{ marketing_url('/stripe') }}" class="text-blue-600 hover:text-blue-700 underline">Stripe</a>, which is PCI-DSS compliant and uses industry-standard encryption.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Do you sell my data?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">No. We don't sell, share, or use your data for advertising. Our built-in analytics are privacy-first and don't use external trackers. If you selfhost, your data stays entirely on your own servers.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical & Selfhosting -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center shadow-lg shadow-gray-700/25">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Technical & Selfhosting</h2>
                </div>

                <div class="space-y-4">
                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Is Event Schedule open source?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Event Schedule is <a href="{{ marketing_url('/open-source') }}" class="text-blue-600 hover:text-blue-700 underline">open source</a> and licensed under the AAL (Attribution Assurance License). You can view the full source code on <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 underline">GitHub</a>, contribute improvements, report issues, or fork it for your own projects.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Can I selfhost Event Schedule?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Download the code from <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 underline">GitHub</a> and run it on your own server. Selfhosting is completely free and includes all features, including Pro features. This gives you complete control over your data and customization options. <a href="{{ marketing_url('/selfhost') }}" class="text-blue-600 hover:text-blue-700 underline">Learn more about selfhosting</a>.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Is there an API?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Yes. Pro users have access to our <a href="{{ marketing_url('/docs/developer/api') }}" class="text-blue-600 hover:text-blue-700 underline">REST API</a>, which lets you programmatically create events, manage schedules, retrieve ticket sales, and integrate with your own systems.</p>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="faq-item bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = !open" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 dark:text-white">What languages does Event Schedule support?</span>
                            <svg aria-hidden="true" :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">The interface is available in 11 languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, and Russian. You can also use <a href="{{ marketing_url('/features/ai') }}" class="text-blue-600 hover:text-blue-700 underline">AI-powered translation</a> to automatically translate your event descriptions.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Still have questions?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                We're here to help. Browse our <a href="{{ route('marketing.docs') }}" class="text-white underline hover:text-white/90">documentation</a> for detailed guides, or reach out and we'll get back to you as soon as possible.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="mailto:contact@eventschedule.com" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                    Contact Us
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-white/20 border border-white/30 rounded-2xl hover:bg-white/30 transition-all">
                    Get Started Free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
