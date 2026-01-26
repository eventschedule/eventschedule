<x-marketing-layout>
    <x-slot name="title">Selling Tickets - Event Schedule</x-slot>
    <x-slot name="description">Learn how to set up and sell tickets for your events. Configure payment methods, create ticket types, and manage sales.</x-slot>
    <x-slot name="keywords">sell tickets, event ticketing, ticket sales, stripe payments, QR code tickets, event management</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Selling Tickets" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Selling Tickets</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Set up ticketing for your events. Connect payment processing, create ticket types, and start selling.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#payment-setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Payment Setup</a>
                        <a href="#create-tickets" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Creating Tickets</a>
                        <a href="#ticket-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Ticket Types</a>
                        <a href="#managing-sales" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Managing Sales</a>
                        <a href="#check-in" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Check-in at the Door</a>
                        <a href="#free-events" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Free Events & RSVPs</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-300 mb-6">Event Schedule includes full ticketing functionality. Sell tickets directly from your event pages with secure payment processing, automatic confirmation emails, and QR code tickets.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Features</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Multiple ticket types per event</li>
                                        <li>Quantity limits and sales deadlines</li>
                                        <li>QR code tickets for easy check-in</li>
                                        <li>Automatic confirmation emails</li>
                                        <li>Sales tracking and reporting</li>
                                        <li>Mobile-friendly checkout</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Ticketing is available on Pro plans. Upgrade your schedule to enable ticket sales.</p>
                            </div>
                        </section>

                        <!-- Payment Setup -->
                        <section id="payment-setup" class="doc-section">
                            <h2 class="doc-heading">Payment Setup</h2>
                            <p class="text-gray-300 mb-6">Before you can sell tickets, you need to connect a payment method. Event Schedule supports several options:</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Stripe</h4>
                                    <p class="text-sm text-gray-400">Accept credit card payments directly. Money goes straight to your Stripe account.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Invoice Ninja</h4>
                                    <p class="text-sm text-gray-400">Connect your Invoice Ninja account for invoicing and payment tracking.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Payment URL</h4>
                                    <p class="text-sm text-gray-400">Use any payment link (PayPal, Venmo, Square, etc.) by entering the URL.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Connecting Stripe</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Settings</strong> → <strong class="text-white">Payment Methods</strong></li>
                                <li>Click <strong class="text-white">"Connect Stripe"</strong></li>
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
                            <p class="text-gray-300 mb-6">Add tickets to any event from the event edit page.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit your event</li>
                                <li>Scroll to the <strong class="text-white">"Tickets"</strong> section</li>
                                <li>Click <strong class="text-white">"Add Ticket Type"</strong></li>
                                <li>Enter ticket details:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Name (e.g., "General Admission", "VIP")</li>
                                        <li>Price</li>
                                        <li>Quantity available (optional)</li>
                                        <li>Description (optional)</li>
                                    </ul>
                                </li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-300">Once tickets are added, a "Get Tickets" button appears on your event page.</p>
                        </section>

                        <!-- Ticket Types -->
                        <section id="ticket-types" class="doc-section">
                            <h2 class="doc-heading">Ticket Types</h2>
                            <p class="text-gray-300 mb-6">Create multiple ticket types to offer different options:</p>

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
                                            <td><span class="font-semibold text-white">General Admission</span></td>
                                            <td>Standard entry ticket</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">VIP</span></td>
                                            <td>Premium tickets with extra benefits</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Early Bird</span></td>
                                            <td>Discounted tickets for early buyers</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Student/Senior</span></td>
                                            <td>Discounted tickets for specific groups</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Table</span></td>
                                            <td>Reserved seating for groups</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Ticket Settings</h3>
                            <ul class="doc-list">
                                <li><strong class="text-white">Quantity:</strong> Limit how many tickets can be sold</li>
                                <li><strong class="text-white">Per-person limit:</strong> Limit how many one person can buy</li>
                                <li><strong class="text-white">Sales end date:</strong> Stop selling tickets at a specific time</li>
                            </ul>
                        </section>

                        <!-- Managing Sales -->
                        <section id="managing-sales" class="doc-section">
                            <h2 class="doc-heading">Managing Sales</h2>
                            <p class="text-gray-300 mb-6">Track and manage your ticket sales from the <strong class="text-white">Sales</strong> page.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">What You Can See</h3>
                            <ul class="doc-list mb-6">
                                <li>List of all purchases with buyer details</li>
                                <li>Payment status (paid, pending, refunded)</li>
                                <li>Check-in status</li>
                                <li>Total revenue</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>
                            <ul class="doc-list">
                                <li><strong class="text-white">Resend tickets:</strong> Send confirmation email again</li>
                                <li><strong class="text-white">Mark as paid:</strong> For cash or external payments</li>
                                <li><strong class="text-white">Cancel/refund:</strong> Cancel a sale (refunds handled in Stripe)</li>
                            </ul>
                        </section>

                        <!-- Check-in -->
                        <section id="check-in" class="doc-section">
                            <h2 class="doc-heading">Check-in at the Door</h2>
                            <p class="text-gray-300 mb-6">Use your phone to scan tickets at the door. No special hardware needed.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <strong class="text-white">Sales</strong> page on your phone</li>
                                <li>Click <strong class="text-white">"Scan Tickets"</strong></li>
                                <li>Point your camera at the QR code on the ticket</li>
                                <li>The app shows the ticket details and marks it as checked in</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Multiple Scanners</div>
                                <p>Any team member with access to your schedule can scan tickets. Just have them log in on their phone.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Ticket Security</h3>
                            <p class="text-gray-300 mb-4">Each ticket has a unique QR code that can only be scanned once. If someone tries to use a ticket that's already been checked in, you'll see a warning.</p>
                        </section>

                        <!-- Free Events -->
                        <section id="free-events" class="doc-section">
                            <h2 class="doc-heading">Free Events & RSVPs</h2>
                            <p class="text-gray-300 mb-6">You can also use the ticketing system for free events to collect RSVPs.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Create a ticket type</li>
                                <li>Set the price to <strong class="text-white">$0</strong> (or leave it blank)</li>
                                <li>Set a quantity limit if you have capacity constraints</li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-300 mb-4">Visitors can "purchase" free tickets to RSVP. They'll receive a confirmation email with a QR code, and you'll have a list of who's coming.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>This is great for capacity management, even for free events. You'll know exactly how many people to expect.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
