<x-marketing-layout>
    <x-slot name="title">Selling Tickets - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Selling Tickets</x-slot>
    <x-slot name="description">Learn how to sell tickets and manage free event registration. Configure payment methods, create ticket types, enable RSVP, and manage sales.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Selling Tickets - Event Schedule",
        "description": "Learn how to set up and sell tickets for your events. Configure payment methods, create ticket types, and manage sales.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2024-01-01",
        "dateModified": "2026-03-08"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Selling Tickets" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Selling Tickets</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up ticketing for your events with zero platform fees. Connect payment processing, create ticket types, and keep 100% of your sales.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <div class="doc-nav-group expanded">
                            <a href="#general" class="doc-nav-group-header doc-nav-link">General <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#external" class="doc-nav-link">External</a>
                                <a href="#registration" class="doc-nav-link">Registration</a>
                                <a href="#ticketing" class="doc-nav-link">Tickets</a>
                                <a href="#ticket-types" class="doc-nav-link">Ticket Types</a>
                                <a href="#free-events" class="doc-nav-link">Free Tickets</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#payment" class="doc-nav-group-header doc-nav-link">Payment <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#invoiceninja-modes" class="doc-nav-link">Invoice Ninja Modes</a>
                            </div>
                        </div>
                        <a href="#options" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Options</a>
                        <a href="#promo-codes" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Promo Codes</a>
                        <div class="doc-nav-group">
                            <a href="#managing-sales" class="doc-nav-group-header doc-nav-link">Managing Sales <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#sale-notifications" class="doc-nav-link">Sale Notifications</a>
                                <a href="#export" class="doc-nav-link">Exporting Sales Data</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#check-in" class="doc-nav-group-header doc-nav-link">Check-in at the Door <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#checkin-dashboard" class="doc-nav-link">Check-in Dashboard</a>
                            </div>
                        </div>
                        <a href="#waitlist" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Waitlist</a>
                        <a href="#feedback" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Post-Event Feedback</a>
                        <a href="#financial" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Financial Information</a>
                        <a href="#embed-widget" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Embed Widget</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- General -->
                        <section id="general" class="doc-section">
                            <h2 class="doc-heading">General</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule includes full ticketing functionality. Sell tickets directly from your event pages with secure payment processing, automatic confirmation emails, and QR code tickets. <strong class="text-gray-900 dark:text-white">Keep 100% of your ticket sales - we never charge platform fees.</strong></p>

                            <x-doc-screenshot id="tickets--sales" alt="Sales management page" loading="eager" />

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Zero Platform Fees</div>
                                <p>Unlike other ticketing platforms that take a cut of every sale, Event Schedule charges nothing. You only pay standard payment processor fees (like Stripe's 2.9% + $0.30).</p>
                            </div>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Features</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Multiple ticket types per event</li>
                                        <li>Quantity limits and sales deadlines</li>
                                        <li>QR code tickets for easy check-in</li>
                                        <li>Automatic confirmation emails</li>
                                        <li>Sales tracking and reporting</li>
                                        <li>Mobile-friendly checkout</li>
                                        <li>Pay-what-you-wish pricing option</li>
                                        <li>Custom checkout fields for attendee info</li>
                                        <li>Promo codes with percentage or fixed discounts</li>
                                        <li>Ticket waitlist for sold-out events</li>
                                        <li>Real-time check-in dashboard</li>
                                        <li>Sale notification emails for organizers</li>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">When editing an event, the Tickets section offers three modes: <strong class="text-gray-900 dark:text-white">External</strong>, <strong class="text-gray-900 dark:text-white">Registration</strong>, and <strong class="text-gray-900 dark:text-white">Tickets</strong>. Choose the mode that fits your event.</p>
                        </section>

                        <!-- External -->
                        <section id="external" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">External</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The default mode. Use this when tickets are sold through an external platform (e.g., Eventbrite, Ticketmaster) or when no ticketing is needed. No payment processing is handled within Event Schedule.</p>

                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Fields</h4>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Registration URL:</strong> A link to your external ticketing page. Displayed as a button on your event page.</li>
                                <li><strong class="text-gray-900 dark:text-white">Price:</strong> An informational display price shown on the event page. Select a currency and enter the amount.</li>
                                <li><strong class="text-gray-900 dark:text-white">Coupon Code:</strong> Displayed alongside the registration URL for attendees to use on the external platform.</li>
                            </ul>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>External mode is available on all plans, including Free.</p>
                            </div>
                        </section>

                        <!-- Registration -->
                        <section id="registration" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Registration</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">A lightweight RSVP system for free events. Attendees sign up with their name and email - no payment setup required.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event and scroll to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
                                <li>Select the <strong class="text-gray-900 dark:text-white">Registration</strong> mode</li>
                                <li>Optionally set an <strong class="text-gray-900 dark:text-white">RSVP Limit</strong> for capacity management</li>
                                <li>Optionally add <strong class="text-gray-900 dark:text-white">Custom Fields</strong> to collect extra information from registrants</li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors will see a "Register" button on your event page. After registering, they receive a confirmation email with a QR code for check-in. You can view all registrations in your sales list.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Attendees can cancel their own registration from the confirmation page linked in their email.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">For recurring events, capacity is tracked per occurrence, so each date has its own registration count against the RSVP limit.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">If you have <x-link href="{{ route('marketing.docs.developer.webhooks') }}">webhooks</x-link> configured, registrations trigger <code class="doc-inline-code">sale.created</code> and cancellations trigger <code class="doc-inline-code">sale.cancelled</code> webhook events.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Registration is available on all plans, including Free. It's perfect for meetups, community events, and open gatherings where you want to know who's coming without the formality of tickets.</p>
                            </div>
                        </section>

                        <!-- Ticketing -->
                        <section id="ticketing" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tickets</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Full ticketing for paid or multi-type events. Create ticket types, connect a payment method, and sell directly from your event page.</p>

                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Ticketing is available on Pro plans. Starting at just $5/month with a 7-day free trial - and zero platform fees on your ticket sales.</p>
                            </div>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event and scroll to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
                                <li>Select the <strong class="text-gray-900 dark:text-white">Tickets</strong> mode</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Add Ticket Type"</strong></li>
                                <li>Enter ticket details:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Name (e.g., "General Admission", "VIP")</li>
                                        <li>Price (or $0 for free tickets, or leave blank for pay-what-you-wish)</li>
                                        <li>Quantity available (leave blank for unlimited)</li>
                                        <li>Description (optional)</li>
                                    </ul>
                                </li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Once tickets are added, a "Get Tickets" button appears on your event page.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Tickets mode has four sub-tabs for configuration:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">General:</strong> Add and manage ticket types</li>
                                <li><strong class="text-gray-900 dark:text-white"><a href="#payment" class="text-cyan-400 hover:text-cyan-300">Payment</a>:</strong> Choose your payment method (Stripe, Invoice Ninja, Payment URL, or Cash)</li>
                                <li><strong class="text-gray-900 dark:text-white"><a href="#options" class="text-cyan-400 hover:text-cyan-300">Options</a>:</strong> Custom checkout fields, ticket notes, terms, and expiration settings</li>
                                <li><strong class="text-gray-900 dark:text-white"><a href="#promo-codes" class="text-cyan-400 hover:text-cyan-300">Promo Codes</a>:</strong> Create discount codes for your tickets</li>
                            </ul>
                        </section>

                        <!-- Ticket Types -->
                        <section id="ticket-types" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ticket Types</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create multiple ticket types to offer different options:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Example</th>
                                            <th>Use Case</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">General Admission</span></td>
                                            <td>Standard entry ticket</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">VIP</span></td>
                                            <td>Premium tickets with extra benefits</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Early Bird</span></td>
                                            <td>Discounted tickets for early buyers</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Student/Senior</span></td>
                                            <td>Discounted tickets for specific groups</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Table</span></td>
                                            <td>Reserved seating for groups</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Pay What You Wish</span></td>
                                            <td>Let attendees choose their price (set minimum optional)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Ticket Settings</h4>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Quantity:</strong> Limit how many tickets can be sold (leave blank for unlimited)</li>
                                <li><strong class="text-gray-900 dark:text-white">Per-person limit:</strong> Limit how many one person can buy</li>
                                <li><strong class="text-gray-900 dark:text-white">Sales end date:</strong> Set a per-ticket-type cutoff to stop selling at a specific time</li>
                                <li><strong class="text-gray-900 dark:text-white">Combined inventory:</strong> Set a total ticket limit across all ticket types for your event</li>
                            </ul>
                        </section>

                        <!-- Free Tickets -->
                        <section id="free-events" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Free Tickets</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">If you need multiple ticket types (e.g. General and VIP) or promo codes for a free event, use the <a href="#ticketing" class="text-cyan-400 hover:text-cyan-300">Tickets</a> mode and set the price to zero:</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Select the <strong class="text-gray-900 dark:text-white">Tickets</strong> mode in the Tickets section</li>
                                <li>Create a ticket type</li>
                                <li>Set the price to <strong class="text-gray-900 dark:text-white">$0</strong> (or leave it blank)</li>
                                <li>Set a quantity limit if you have capacity constraints</li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors can "purchase" free tickets to RSVP. They'll receive a confirmation email with a QR code, and you'll have a list of who's coming.</p>

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Tip</div>
                                <p>For simple free events where you only need a headcount, use the <a href="#registration" class="text-cyan-400 hover:text-cyan-300">Registration</a> mode instead - it's simpler and available on all plans.</p>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Registration and ticketing are mutually exclusive on a single event. If you need both free and paid options, use the ticketing system with a $0 ticket type alongside your paid tickets.</p>
                            </div>
                        </section>

                        <!-- Payment -->
                        <section id="payment" class="doc-section">
                            <h2 class="doc-heading">Payment</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Before you can sell tickets, you need to connect a payment method. Event Schedule supports several options:</p>

                            <div class="space-y-4 mb-6" id="payment-setup">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stripe</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Accept credit card payments directly. Money goes straight to your Stripe account. Set up Stripe in <a href="{{ route('marketing.docs.account_settings') }}#payments" class="text-cyan-400 hover:text-cyan-300">Account Settings</a>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice Ninja</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Invoice Ninja account for invoicing and payment tracking.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment URL</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Use any payment link (PayPal, Venmo, Square, etc.) by entering the URL.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cash</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Accept payment at the door. Add optional payment instructions for attendees.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connecting Stripe</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Plan</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Connect Stripe"</strong></li>
                                <li>Complete the Stripe onboarding process</li>
                                <li>Once connected, Stripe will be available as a payment option</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Recommended</div>
                                <p>We recommend using Stripe with Invoice Ninja for the best experience. Invoice Ninja provides additional features like invoicing, payment reminders, and financial reporting.</p>
                            </div>
                        </section>

                        <!-- Invoice Ninja Modes -->
                        <section id="invoiceninja-modes" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice Ninja Modes</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">When using Invoice Ninja, choose between two checkout modes in your <a href="{{ route('marketing.docs.account_settings') }}#payments" class="text-cyan-400 hover:text-cyan-300">payment settings</a>.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice Mode</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Ticket selection and promo codes are handled in Event Schedule. An invoice is created in Invoice Ninja for each purchase. Supports multiple promo codes and per-ticket promo targeting. Buyers can optionally create an Event Schedule account during checkout.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Link Mode</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Buyers select tickets and enter promo codes on the Invoice Ninja purchase page. Invoices are grouped in Invoice Ninja, making bulk management easier. Supports one promo code per event (applied to all tickets). Buyers can optionally create an Event Schedule account during checkout. See the <x-link href="https://invoiceninja.github.io/docs/user-guide/subscriptions" target="_blank">Invoice Ninja payment link docs</x-link> for more details.</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Invoice</th>
                                            <th>Payment Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Ticket selection</td>
                                            <td>Event Schedule</td>
                                            <td>Invoice Ninja</td>
                                        </tr>
                                        <tr>
                                            <td>Promo code entry</td>
                                            <td>Event Schedule</td>
                                            <td>Invoice Ninja</td>
                                        </tr>
                                        <tr>
                                            <td>Multiple promo codes</td>
                                            <td>Yes</td>
                                            <td>One per event</td>
                                        </tr>
                                        <tr>
                                            <td>Per-ticket promo targeting</td>
                                            <td>Yes</td>
                                            <td>No</td>
                                        </tr>
                                        <tr>
                                            <td>Invoices grouped in IN</td>
                                            <td>No</td>
                                            <td>Yes</td>
                                        </tr>
                                        <tr>
                                            <td>Account creation</td>
                                            <td>Yes</td>
                                            <td>Yes</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Start with invoice mode for maximum flexibility. Switch to payment link mode if you want invoices grouped together in Invoice Ninja.</p>
                            </div>
                        </section>

                        <!-- Options -->
                        <section id="options" class="doc-section">
                            <h2 class="doc-heading">Options</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure additional checkout settings for your event's tickets.</p>

                            <h3 id="checkout-fields" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom Checkout Fields</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Collect additional information from attendees during checkout. You can add up to 10 custom fields per event.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event</li>
                                <li>Go to the <strong class="text-gray-900 dark:text-white">Tickets &rarr; Options</strong> tab</li>
                                <li>Add field labels (e.g., "Dietary Requirements", "T-Shirt Size")</li>
                                <li>Mark fields as required or optional</li>
                                <li>Save the event</li>
                            </ol>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Common Use Cases</h4>
                                <ul class="doc-list text-sm">
                                    <li>Dietary restrictions for catered events</li>
                                    <li>T-shirt sizes for swag</li>
                                    <li>Company name for business events</li>
                                    <li>Emergency contact information</li>
                                    <li>How did you hear about us?</li>
                                </ul>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Responses are stored with each sale and can be viewed in your sales dashboard or exported.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Settings</h3>
                            <ul class="doc-list">
                                <li><strong class="text-gray-900 dark:text-white">Ticket notes:</strong> Add notes that appear on the ticket (e.g., parking instructions, what to bring)</li>
                                <li><strong class="text-gray-900 dark:text-white">Terms URL:</strong> Link to your terms and conditions. Buyers must agree before purchasing.</li>
                                <li><strong class="text-gray-900 dark:text-white">Ticket sales end:</strong> Set a date and time per ticket type when sales automatically stop. Use this to create time-based pricing tiers (e.g. early bird ending before regular tickets).</li>
                                <li><strong class="text-gray-900 dark:text-white">Expire unpaid tickets:</strong> Automatically release unpaid tickets after a set number of hours, making them available for other buyers</li>
                            </ul>
                        </section>

                        <!-- Promo Codes -->
                        <section id="promo-codes" class="doc-section">
                            <h2 class="doc-heading">Promo Codes</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Offer discounts to attendees with promo codes. Buyers enter a code during checkout to receive a discount on their purchase.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adding a Promo Code</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event</li>
                                <li>Go to the <strong class="text-gray-900 dark:text-white">Tickets &rarr; Promo Codes</strong> tab</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Add Promo Code"</strong></li>
                                <li>Enter the code (e.g., "EARLYBIRD", "VIP50")</li>
                                <li>Choose the discount type and value</li>
                                <li>Save the event</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Discount Types</h3>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Percentage:</strong> A percentage off the ticket price (e.g., 20% off)</li>
                                <li><strong class="text-gray-900 dark:text-white">Fixed amount:</strong> A flat amount off the ticket price (e.g., $10 off)</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Promo Code Settings</h3>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Max uses:</strong> Limit how many times the code can be used (leave blank for unlimited)</li>
                                <li><strong class="text-gray-900 dark:text-white">Expiration date:</strong> Set a date and time when the code stops working</li>
                                <li><strong class="text-gray-900 dark:text-white">Active/inactive:</strong> Toggle the code on or off without deleting it</li>
                                <li><strong class="text-gray-900 dark:text-white">Applies to:</strong> Apply the code to all ticket types, or target specific tickets only</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Each promo code generates a shareable link that pre-fills the code at checkout, making it easy to distribute to your audience.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Invoice Ninja Payment Link Mode</div>
                                <p>When using Invoice Ninja in payment link mode, only one promo code per event is supported and it applies to all ticket types. Use invoice mode for multiple promo codes with per-ticket targeting.</p>
                            </div>
                        </section>

                        <!-- Managing Sales -->
                        <section id="managing-sales" class="doc-section">
                            <h2 class="doc-heading">Managing Sales</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Track and manage your ticket sales from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong>.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">What You Can See</h3>
                            <ul class="doc-list mb-6">
                                <li>List of all purchases with buyer details</li>
                                <li>Payment status (paid, pending, refunded)</li>
                                <li>Check-in status</li>
                                <li>Total revenue</li>
                            </ul>

                            <h3 id="filtering-sales" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtering Sales</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Use the filter input at the top of the sales list to search by buyer name, email, or event name. The filter updates results in real time. When exporting sales data, only the currently filtered results are included in the export.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                            <ul class="doc-list">
                                <li><strong class="text-gray-900 dark:text-white">Resend tickets:</strong> Send confirmation email again</li>
                                <li><strong class="text-gray-900 dark:text-white">Mark as paid:</strong> For cash or external payments</li>
                                <li><strong class="text-gray-900 dark:text-white">Cancel/refund:</strong> Cancel a sale (refunds handled in Stripe)</li>
                                <li><strong class="text-gray-900 dark:text-white">Delete:</strong> Permanently remove a sale record</li>
                            </ul>
                        </section>

                        <!-- Sale Notifications -->
                        <section id="sale-notifications" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sale Notification Emails</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Opt in to receive an email notification every time a ticket sells. Each notification includes:</p>

                            <ul class="doc-list mb-6">
                                <li>Buyer name and email</li>
                                <li>Ticket type and quantity</li>
                                <li>Total amount</li>
                                <li>Payment status</li>
                                <li>Discount or promo code applied</li>
                            </ul>

                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How to Enable</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Settings &rarr; Notifications</strong> and enable sale notification emails.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Sender & Compliance</div>
                                <p>If you have a custom sender email configured for your schedule, sale notifications are sent from that address. All notification emails include an unsubscribe link for compliance.</p>
                            </div>
                        </section>

                        <!-- Export -->
                        <section id="export" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exporting Sales Data</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Export your sales data for accounting, tax purposes, or to import into other systems.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong></li>
                                <li>Filter by event or date range if needed</li>
                                <li>Click the <strong class="text-gray-900 dark:text-white">"Export"</strong> button</li>
                                <li>Download your sales data as a spreadsheet</li>
                            </ol>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Export Includes</h4>
                                <ul class="doc-list text-sm">
                                    <li>Buyer name and email</li>
                                    <li>Ticket type and quantity</li>
                                    <li>Purchase date and amount</li>
                                    <li>Transaction reference</li>
                                    <li>Payment method and status</li>
                                    <li>Promo code and discount amount</li>
                                    <li>Check-in status</li>
                                    <li>Custom checkout field responses (event-level and ticket-level)</li>
                                </ul>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>The CSV includes a byte order mark (BOM) for Excel compatibility. Export your sales data regularly for your records - this is especially useful for tax reporting and financial reconciliation.</p>
                            </div>
                        </section>

                        <!-- Check-in -->
                        <section id="check-in" class="doc-section">
                            <h2 class="doc-heading">Check-in at the Door</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Use your phone to scan tickets at the door. No special hardware needed.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> on your phone</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Scan Tickets"</strong></li>
                                <li>Point your camera at the QR code on the ticket</li>
                                <li>The app shows the ticket details and marks it as checked in</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Multiple Scanners</div>
                                <p>Any team member with access to your schedule can scan tickets. Just have them log in on their phone.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ticket Security</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Each ticket has a unique QR code that can only be scanned once. If someone tries to use a ticket that's already been checked in, you'll see a warning.</p>
                        </section>

                        <!-- Check-in Dashboard -->
                        <section id="checkin-dashboard" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Check-in Dashboard</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Monitor attendance in real time from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Check-in</strong>. The dashboard provides a live overview of check-in progress for your event.</p>

                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Overall progress bar</strong> with percentage of attendees checked in</li>
                                <li><strong class="text-gray-900 dark:text-white">Per-ticket-type breakdown</strong> showing check-in counts for each ticket type</li>
                                <li><strong class="text-gray-900 dark:text-white">Recent activity feed</strong> showing the last 10 check-ins with attendee names and times</li>
                                <li><strong class="text-gray-900 dark:text-white">Filter by event and event date</strong> to view specific event dates</li>
                            </ul>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Auto-Refresh</div>
                                <p>The dashboard auto-refreshes every 10 seconds, so you always see the latest check-in data. It works on any device, including phones and tablets.</p>
                            </div>
                        </section>

                        <!-- Waitlist -->
                        <section id="waitlist" class="doc-section">
                            <h2 class="doc-heading">Ticket Waitlist</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">When tickets sell out, fans can join a waitlist to be notified when spots become available.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>When all tickets sell out for an event date, a <strong class="text-gray-900 dark:text-white">"Join Waitlist"</strong> button appears on the event page</li>
                                <li>Guests enter their name and email</li>
                                <li>When tickets become available (sale cancelled, refunded, or expired), the next person in line is notified via email</li>
                                <li>They receive a 24-hour link to purchase</li>
                                <li>If they don't purchase in time, the next person in line is notified</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Managing the Waitlist</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">View and manage the waitlist from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Waitlist</strong>. The table shows each entry's name, email, event, date, and status.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">One at a Time</div>
                                <p>Only one person is notified at a time to prevent overselling. The next person is notified only after the current person's 24-hour window expires or they complete their purchase.</p>
                            </div>
                        </section>

                        <!-- Post-Event Feedback -->
                        <section id="feedback" class="doc-section">
                            <h2 class="doc-heading">Post-Event Feedback <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Automatically collect ratings and comments from attendees after your events end. Feedback emails are sent to ticket buyers and RSVP attendees, linking to a simple form where they can rate their experience.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Enabling Feedback</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's edit page in the admin panel</li>
                                <li>Open <strong class="text-gray-900 dark:text-white">Settings &rarr; Notifications</strong></li>
                                <li>Enable <strong class="text-gray-900 dark:text-white">Post-event feedback</strong></li>
                                <li>Choose a delay (how long after the event ends before emails are sent). The default is 24 hours.</li>
                                <li>Save your changes</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Per-Event Override</div>
                                <p>You can override the schedule-level setting for individual events. In the event edit page, scroll to the <strong>Feedback</strong> section and choose "Enabled" or "Disabled" to override, or "Use schedule default" to follow the schedule setting.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>After an event ends and the configured delay passes, feedback request emails are automatically sent to attendees</li>
                                <li>Each email contains a link to a feedback form branded with your schedule's logo and colors</li>
                                <li>Attendees rate their experience from 1 to 5 stars and can leave an optional comment</li>
                                <li>Each attendee can only submit feedback once</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Viewing Feedback</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">View all feedback from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales &rarr; Feedback</strong> tab. The page shows:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Summary card</strong> with average rating, total responses, and response rate</li>
                                <li><strong class="text-gray-900 dark:text-white">Feedback table</strong> listing each response with attendee name, event, date, star rating, comment, and submission time</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exporting Feedback</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Click <strong class="text-gray-900 dark:text-white">Export Feedback</strong> on the Feedback tab to download a CSV file with all feedback data.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Feedback Notifications</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">To receive an email when new feedback is submitted, enable <strong class="text-gray-900 dark:text-white">New feedback</strong> in <strong class="text-gray-900 dark:text-white">Settings &rarr; Notifications</strong>. Each notification includes the event name, attendee name, star rating, and comment.</p>
                        </section>

                        <!-- Financial Information -->
                        <section id="financial" class="doc-section">
                            <h2 class="doc-heading">Financial Information</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Important information about payments, refunds, and taxes.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Refunds</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Refunds are handled through your payment provider (Stripe or Invoice Ninja). Event Schedule marks the sale as cancelled, but you must process the actual refund in your Stripe Dashboard or Invoice Ninja account. Stripe refunds appear on customer statements within 5-10 business days.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Taxes</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Event Schedule does not automatically calculate or collect sales tax. Set your ticket prices inclusive of any applicable taxes. For tax reporting, export your sales data from the Sales page. Consult a tax professional for your specific obligations.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Processing Fees</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Stripe charges their standard processing fees (typically 2.9% + $0.30 per transaction in the US). These fees are deducted from your payouts. Event Schedule does not charge additional fees for ticketing.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled or Deleted Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">If you delete an event with sold tickets, ticket holders are <strong class="text-gray-900 dark:text-white">not</strong> automatically notified or refunded. Before deleting, you should: (1) contact ticket holders about the cancellation, (2) process refunds through your payment provider, and (3) then delete the event. Sales data is preserved even after event deletion.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payout Schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Stripe pays out on a rolling basis (typically 2 business days in the US, varies by country). View your payout schedule and history in your Stripe Dashboard. Invoice Ninja follows your configured payment terms.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Record Keeping</div>
                                <p>Export your sales data regularly from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> for your records. This includes buyer information, ticket types, and payment status.</p>
                            </div>
                        </section>

                        <!-- Embed Widget -->
                        <section id="embed-widget" class="doc-section">
                            <h2 class="doc-heading">Embed Widget <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Embed a ticket purchase or RSVP form directly on your own website using an iframe. Visitors can buy tickets or register without leaving your site.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Getting the Embed Code</h3>
                            <ol class="doc-list list-decimal mb-6">
                                <li>Open your event in the admin panel and go to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
                                <li>Enable <strong class="text-gray-900 dark:text-white">Tickets</strong> or <strong class="text-gray-900 dark:text-white">Registration</strong> mode</li>
                                <li>Click the <strong class="text-gray-900 dark:text-white">Embed Tickets</strong> (or <strong class="text-gray-900 dark:text-white">Embed Registration</strong>) link next to the section heading</li>
                                <li>Copy the iframe code and paste it into your website's HTML</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">URL Parameters</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">You can customize the embed URL with these parameters:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-white/10">
                                            <th class="text-left py-2 pr-4 font-semibold text-gray-900 dark:text-white">Parameter</th>
                                            <th class="text-left py-2 font-semibold text-gray-900 dark:text-white">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">tickets=true</code></td>
                                            <td class="py-2">Show the ticket purchase form</td>
                                        </tr>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">rsvp=true</code></td>
                                            <td class="py-2">Show the RSVP registration form</td>
                                        </tr>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">embed=true</code></td>
                                            <td class="py-2">Enable embed mode (compact layout, no navigation)</td>
                                        </tr>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">dark=true</code></td>
                                            <td class="py-2">Force dark mode</td>
                                        </tr>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">promo=CODE</code></td>
                                            <td class="py-2">Pre-fill a promo code</td>
                                        </tr>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-2 pr-4"><code class="doc-code">lang=xx</code></td>
                                            <td class="py-2">Set the widget language (e.g., <code class="doc-code">lang=es</code> for Spanish)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Private Events</div>
                                <p>The embed widget is not available for private events since they require authentication to access.</p>
                            </div>

                            <div class="doc-callout doc-callout-info mt-4">
                                <div class="doc-callout-title">Payment Redirects</div>
                                <p>Stripe, Invoice Ninja, and custom payment URL checkouts will open in the parent window (outside the iframe) since external payment portals may not support being loaded inside iframes. Cash and free ticket checkouts complete inside the embed.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to sell tickets for</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Promote your events</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Create promotional images</li>
                                <li><a href="{{ route('marketing.docs.analytics') }}" class="text-cyan-400 hover:text-cyan-300">Analytics</a> - Track conversion rates and revenue per view</li>
                                <li><a href="{{ route('marketing.docs.account_settings') }}" class="text-cyan-400 hover:text-cyan-300">Account Settings</a> - Set up your payment method</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">Newsletters</a> - Send newsletters to promote ticket sales</li>
                                <li><a href="{{ marketing_url('/features/embed-tickets') }}" class="text-cyan-400 hover:text-cyan-300">Embed Tickets</a> - Embed a ticket form on your website</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to Sell Tickets with Event Schedule",
        "description": "Set up ticketing for your events with payment processing, ticket types, and QR code check-ins.",
        "totalTime": "PT10M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Connect Stripe",
                "text": "Go to Admin Panel, then Plan, and click Connect Stripe. Complete the Stripe onboarding process.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#payment-setup"
            },
            {
                "@type": "HowToStep",
                "name": "Create Ticket Types",
                "text": "Edit your event, scroll to Tickets section, click Add Ticket Type. Enter name, price, quantity, and description.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#create-tickets"
            },
            {
                "@type": "HowToStep",
                "name": "Manage Sales",
                "text": "View all purchases, payment status, and check-in status from Admin Panel, then Sales.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#managing-sales"
            },
            {
                "@type": "HowToStep",
                "name": "Track Check-ins",
                "text": "Use the real-time check-in dashboard at Admin Panel, then Check-in, to monitor attendance with progress bars and a live activity feed.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#checkin-dashboard"
            },
            {
                "@type": "HowToStep",
                "name": "Check In Attendees",
                "text": "Go to Admin Panel, then Sales on your phone, click Scan Tickets, and point your camera at the QR code.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#check-in"
            }
        ]
    }
    </script>
</x-marketing-layout>
