<x-marketing-layout>
    <x-slot name="title">Selling Tickets - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Selling Tickets</x-slot>
    <x-slot name="description">Learn how to set up and sell tickets for your events. Configure payment methods, create ticket types, and manage sales.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
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
        }
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
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#payment-setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Payment Setup</a>
                        <a href="#create-tickets" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Creating Tickets</a>
                        <a href="#ticket-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Ticket Types</a>
                        <a href="#checkout-fields" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Custom Checkout Fields</a>
                        <a href="#managing-sales" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Managing Sales</a>
                        <a href="#export" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Exporting Sales Data</a>
                        <a href="#check-in" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Check-in at the Door</a>
                        <a href="#free-events" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Free Events & RSVPs</a>
                        <a href="#financial" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Financial Information</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule includes full ticketing functionality. Sell tickets directly from your event pages with secure payment processing, automatic confirmation emails, and QR code tickets. <strong class="text-gray-900 dark:text-white">Keep 100% of your ticket sales - we never charge platform fees.</strong></p>

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
                                    </ul>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Ticketing is available on Pro plans. Starting at just $5/month with a 7-day free trial - and zero platform fees on your ticket sales.</p>
                            </div>
                        </section>

                        <!-- Payment Setup -->
                        <section id="payment-setup" class="doc-section">
                            <h2 class="doc-heading">Payment Setup</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Before you can sell tickets, you need to connect a payment method. Event Schedule supports several options:</p>

                            <div class="space-y-4 mb-6">
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

                        <!-- Creating Tickets -->
                        <section id="create-tickets" class="doc-section">
                            <h2 class="doc-heading">Creating Tickets</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add tickets to any event from the event edit page.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event</li>
                                <li>Scroll to the <strong class="text-gray-900 dark:text-white">"Tickets"</strong> section</li>
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

                            <p class="text-gray-600 dark:text-gray-300">Once tickets are added, a "Get Tickets" button appears on your event page.</p>
                        </section>

                        <!-- Ticket Types -->
                        <section id="ticket-types" class="doc-section">
                            <h2 class="doc-heading">Ticket Types</h2>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ticket Settings</h3>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Quantity:</strong> Limit how many tickets can be sold (leave blank for unlimited)</li>
                                <li><strong class="text-gray-900 dark:text-white">Per-person limit:</strong> Limit how many one person can buy</li>
                                <li><strong class="text-gray-900 dark:text-white">Sales end date:</strong> Stop selling tickets at a specific time</li>
                                <li><strong class="text-gray-900 dark:text-white">Combined inventory:</strong> Set a total ticket limit across all ticket types for your event</li>
                            </ul>
                        </section>

                        <!-- Custom Checkout Fields -->
                        <section id="checkout-fields" class="doc-section">
                            <h2 class="doc-heading">Custom Checkout Fields</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Collect additional information from attendees during checkout. You can add up to 8 custom fields per event.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setting Up Custom Fields</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event</li>
                                <li>Scroll to the <strong class="text-gray-900 dark:text-white">"Checkout Fields"</strong> section</li>
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

                            <p class="text-gray-600 dark:text-gray-300">Responses are stored with each sale and can be viewed in your sales dashboard or exported.</p>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                            <ul class="doc-list">
                                <li><strong class="text-gray-900 dark:text-white">Resend tickets:</strong> Send confirmation email again</li>
                                <li><strong class="text-gray-900 dark:text-white">Mark as paid:</strong> For cash or external payments</li>
                                <li><strong class="text-gray-900 dark:text-white">Cancel/refund:</strong> Cancel a sale (refunds handled in Stripe)</li>
                            </ul>
                        </section>

                        <!-- Export -->
                        <section id="export" class="doc-section">
                            <h2 class="doc-heading">Exporting Sales Data</h2>
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
                                    <li>Payment status</li>
                                    <li>Check-in status</li>
                                    <li>Custom checkout field responses</li>
                                </ul>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Export your sales data regularly for your records. This is especially useful for tax reporting and financial reconciliation.</p>
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

                        <!-- Free Events -->
                        <section id="free-events" class="doc-section">
                            <h2 class="doc-heading">Free Events & RSVPs</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can also use the ticketing system for free events to collect RSVPs.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Create a ticket type</li>
                                <li>Set the price to <strong class="text-gray-900 dark:text-white">$0</strong> (or leave it blank)</li>
                                <li>Set a quantity limit if you have capacity constraints</li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors can "purchase" free tickets to RSVP. They'll receive a confirmation email with a QR code, and you'll have a list of who's coming.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>This is great for capacity management, even for free events. You'll know exactly how many people to expect.</p>
                            </div>
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
                "name": "Check In Attendees",
                "text": "Go to Admin Panel, then Sales on your phone, click Scan Tickets, and point your camera at the QR code.",
                "url": "{{ url(route('marketing.docs.tickets')) }}#check-in"
            }
        ]
    }
    </script>
</x-marketing-layout>
