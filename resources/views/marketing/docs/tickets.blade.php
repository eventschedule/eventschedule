<x-docs-page
    key="tickets"
    description="Learn how to sell tickets and manage free event registration. Configure payment methods, create ticket types, enable RSVP, and manage sales."
    lede="Selling is included on every plan, with zero platform fees. Connect payment processing, create ticket types, and keep 100% of your sales."
    article-description="Learn how to set up and sell tickets for your events. Configure payment methods, create ticket types, and manage sales."
>
    <x-slot:toc>
        <x-doc-nav-group label="General" href="#general" expanded>
            <x-doc-nav-link href="#external">External</x-doc-nav-link>
            <x-doc-nav-link href="#registration">Registration</x-doc-nav-link>
            <x-doc-nav-link href="#ticketing">Tickets</x-doc-nav-link>
            <x-doc-nav-link href="#ticket-types">Ticket Types</x-doc-nav-link>
            <x-doc-nav-link href="#free-events">Free Tickets</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Payment" href="#payment">
            <x-doc-nav-link href="#payfast">Payfast</x-doc-nav-link>
            <x-doc-nav-link href="#invoiceninja-modes">Invoice Ninja Modes</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#options">Options</x-doc-nav-link>
        <x-doc-nav-link href="#installments">Installment Payments</x-doc-nav-link>
        <x-doc-nav-link href="#promo-codes">Promo Codes</x-doc-nav-link>
        <x-doc-nav-link href="#add-ons">Add-ons</x-doc-nav-link>
        <x-doc-nav-link href="#allocated-seating">Allocated Seating</x-doc-nav-link>
        <x-doc-nav-group label="Managing Sales" href="#managing-sales">
            <x-doc-nav-link href="#sale-notifications">Sale Notifications</x-doc-nav-link>
            <x-doc-nav-link href="#export">Exporting Sales Data</x-doc-nav-link>
            <x-doc-nav-link href="#importing-attendees">Importing Attendees</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Check-in at the Door" href="#check-in">
            <x-doc-nav-link href="#checkin-dashboard">Check-in Dashboard</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#waitlist">Waitlist</x-doc-nav-link>
        <x-doc-nav-link href="#feedback">Post-Event Feedback</x-doc-nav-link>
        <x-doc-nav-link href="#financial">Financial Information</x-doc-nav-link>
        <x-doc-nav-link href="#embed-widget">Embed Widget</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- General -->
    <section id="general" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>
            General
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Sell tickets directly from your event pages with secure payment processing, automatic confirmation emails, and a QR code on every ticket. <strong class="text-gray-900 dark:text-white">Selling is included on every plan, including Free, and Event Schedule takes no cut of a ticket sale.</strong></p>

        <x-doc-screenshot id="tickets--sales" alt="Sales management page" loading="eager" />

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Zero platform fees, on every plan</div>
            <p>The checkout charge is created on <em>your own</em> connected Stripe account, with no application fee attached, so nothing is skimmed on the way through. This is the same on Free as it is on Pro: you pay only your payment processor's own fees. A selfhosted install charges through its own Stripe keys, which works the same way.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">When you edit an event, the <strong class="text-gray-900 dark:text-white">Tickets</strong> section offers three mutually exclusive modes. Pick the one that fits the event:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Mode</th>
                        <th>What it does</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#external" class="doc-link">External</a></td>
                        <td>Sends visitors to someone else's ticketing page, or to no ticketing at all. The default.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td><a href="#registration" class="doc-link">Registration</a></td>
                        <td>A name-and-email RSVP with an optional capacity limit per date. Unlimited on every plan.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td><a href="#ticketing" class="doc-link">Tickets</a></td>
                        <td>Ticket types with prices, quantities and checkout. Free tickets are unlimited; paid tickets have a monthly allowance on the Free plan.</td>
                        <td>Free, capped</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">The Free plan's paid-ticket allowance</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A Free schedule can sell paid tickets, up to <strong class="text-gray-900 dark:text-white">25 paid tickets per calendar month</strong>. There is also a backstop of 50 paid tickets a month across every schedule one account owns, so the per-schedule allowance cannot be multiplied by spreading events over several schedules. <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> and Enterprise remove both limits, and a <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install is unlimited.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The allowance counts individual paid tickets, not orders, and several things never count against it:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Free registration and RSVP</strong> - unlimited on every plan</li>
            <li><strong class="text-gray-900 dark:text-white">Zero-price ticket types</strong> - a $0 tier sells without limit, even on an event that also has paid tiers</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="#add-ons" class="doc-link">Add-ons</a></strong> - extras, not admissions</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointment bookings</a></strong> - they have their own separate allowance</li>
            <li><strong class="text-gray-900 dark:text-white">Bulk <a href="#importing-attendees" class="doc-link">attendee imports</a></strong></li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Two rules keep the allowance from landing at the worst possible moment:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Cash and other offline payments are counted but never blocked.</strong> Money taken at the door is always recordable, whatever the count says.</li>
            <li><strong class="text-gray-900 dark:text-white">An event starting within 48 hours is exempt.</strong> The allowance never stops sales for an event that is about to happen.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The count resets on the first of each month. If a paid plan lapsed part-way through a month, the window starts from the moment it lapsed, so tickets you sold while paying are not charged to the Free allowance. When the allowance is spent, online paid checkout pauses for that schedule until the reset: free registration, free ticket tiers and payment at the door all keep working, and the event page falls back to an <strong class="text-gray-900 dark:text-white">Add to Calendar</strong> button instead of a dead buy button. The event editor shows the running count and the reset date in the Tickets section.</p>

        <h3 class="doc-subheading">What ticketing includes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Everything below works on the Free plan except where a Pro badge says otherwise.</p>
        <ul class="doc-list">
            <li>Any number of ticket types per event, each with its own price, quantity and description</li>
            <li>Per-type sales start and end times, and an optional cap on how many one order may hold</li>
            <li>Volume discounts on a ticket type</li>
            <li><a href="#promo-codes" class="doc-link">Promo codes</a> and <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">gift cards</a> <x-doc-badge plan="pro" /></li>
            <li>Optional <a href="#add-ons" class="doc-link">add-ons</a> such as parking or merchandise <x-doc-badge plan="pro" /></li>
            <li><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Passes and subscriptions</a> that one buyer reuses across many events <x-doc-badge plan="pro" /></li>
            <li>Custom checkout fields, collected once per order or once per ticket <x-doc-badge plan="pro" /></li>
            <li>A QR code on every ticket and phone scanning at the door, on every plan, plus a live <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a> <x-doc-badge plan="pro" /></li>
            <li>A <a href="#waitlist" class="doc-link">waitlist</a> that opens automatically when an event date sells out (free for registration, <x-doc-badge plan="pro" /> for tickets)</li>
            <li>Sale notification emails, a <a href="#export" class="doc-link">CSV export</a> and a bulk <a href="#importing-attendees" class="doc-link">attendee import</a> <x-doc-badge plan="pro" /></li>
            <li><a href="#feedback" class="doc-link">Post-event feedback</a> requests with star ratings <x-doc-badge plan="pro" /></li>
        </ul>
    </section>

    <!-- External -->
    <section id="external" class="doc-section">
        <h3 class="doc-subheading">External</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The default mode. Use it when tickets are sold somewhere else (Eventbrite, Ticketmaster, a box office of your own) or when the event needs no ticketing at all. Event Schedule handles no money in this mode.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Fields</h4>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Registration URL:</strong> the external ticketing page. It becomes a <strong class="text-gray-900 dark:text-white">View Event</strong> button on your event page, opening in a new tab.</li>
            <li><strong class="text-gray-900 dark:text-white">Price:</strong> a display-only price with a currency. Leave it blank if you do not know it; enter <code class="doc-inline-code">0</code> and the event page reads "Free entry".</li>
            <li><strong class="text-gray-900 dark:text-white">Coupon Code:</strong> shown under the price so attendees can use it on the external platform. Event Schedule never validates it.</li>
            <li><strong class="text-gray-900 dark:text-white">Discount:</strong> what the coupon is worth, as a percentage or an amount in the event's currency. Shown beside the code, so the event page can read <code class="doc-inline-code">SAVE20 &bull; 15% off</code> rather than sending guests to the external site to find out. Leave it blank if the coupon has no fixed value.</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>External mode is available on every plan, including Free. The price row only appears on the event page once a Registration URL is set.</p>
        </div>
    </section>

    <!-- Registration -->
    <section id="registration" class="doc-section">
        <h3 class="doc-subheading">Registration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A lightweight RSVP system for free events. Attendees sign up with their name and email - no payment setup required.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event and scroll to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
            <li>Select the <strong class="text-gray-900 dark:text-white">Registration</strong> mode</li>
            <li>Optionally set a <strong class="text-gray-900 dark:text-white">Registration Limit</strong> to cap how many people can sign up</li>
            <li>Optionally add <strong class="text-gray-900 dark:text-white">Custom Fields</strong> to collect extra details (Pro)</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors then see a <strong class="text-gray-900 dark:text-white">Register</strong> button on your event page. After registering they receive a confirmation email with a QR code for check-in, and the registration appears in your sales list. Add <strong class="text-gray-900 dark:text-white">Registration notes</strong> (on the <a href="#options" class="doc-link">Options</a> tab) to put directions, parking, a dress code or anything else into that email and onto their ticket.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Registrants can cancel themselves from the ticket page linked in their email, which frees the spot again.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">The Registration Limit is per date.</strong> On a recurring event each occurrence keeps its own count, so a limit of 30 means 30 people per date, not 30 across the series.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">If you have <x-link href="{{ route('marketing.docs.developer.webhooks') }}">webhooks</x-link> configured, registrations fire <code class="doc-inline-code">sale.created</code> and cancellations fire <code class="doc-inline-code">sale.cancelled</code>.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Registration is unlimited on every plan, including Free, and never counts toward the <a href="#general" class="doc-link">paid-ticket allowance</a>. So are the <a href="#waitlist" class="doc-link">waitlist</a> on a full registration date, per-guest individual registration, and the <a href="#embed-widget" class="doc-link">RSVP embed widget</a>. Registration suits meetups, community events and open gatherings where you want to know who is coming without the formality of tickets. Custom checkout fields are the one part that needs Pro.</p>
        </div>
    </section>

    <!-- Ticketing -->
    <section id="ticketing" class="doc-section">
        <h3 class="doc-subheading">Tickets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Full ticketing for paid or multi-type events. Create ticket types, connect a payment method, and sell directly from your event page.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">What Free includes, and what Pro adds</div>
            <p>Creating ticket types, taking payment, scanning tickets at the door and keeping 100% of the money all work on the Free plan, within the <a href="#general" class="doc-link">25 paid tickets a month</a> allowance. Pro removes that ceiling and unlocks the surrounding toolkit: the live check-in dashboard, promo codes, add-ons, passes, individual tickets, the ticket waitlist, the CSV export, the bulk import, gift cards and post-event feedback. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so nothing here is held back there.</p>
        </div>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Capability</th>
                        <th>Free</th>
                        <th>Pro and above</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Paid ticket sales</td>
                        <td>25 per month, per schedule</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Platform fee on a sale</td>
                        <td>None</td>
                        <td>None</td>
                    </tr>
                    <tr>
                        <td>Free tickets, registration and RSVP</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Ticket types, quantities, sales windows, volume discounts, max per order</td>
                        <td>Yes</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>QR codes and <a href="#check-in" class="doc-link">scanning at the door</a></td>
                        <td>Yes</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>Live <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a></td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#promo-codes" class="doc-link">Promo codes</a>, <a href="#add-ons" class="doc-link">add-ons</a>, <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">gift cards</a></td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Passes</a>, individual tickets, custom checkout fields</td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>Ticket <a href="#waitlist" class="doc-link">waitlist</a>, <a href="#export" class="doc-link">CSV export</a>, <a href="#importing-attendees" class="doc-link">bulk import</a>, <a href="#embed-widget" class="doc-link">ticket embed</a></td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Setting up ticket sales</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Connect a payment method first, under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong>. Without one, the only choice on the Payment tab is Cash. See <a href="#payment" class="doc-link">Payment</a>.</li>
            <li>Edit your event and scroll to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
            <li>Select the <strong class="text-gray-900 dark:text-white">Tickets</strong> mode</li>
            <li>Fill in the first ticket type (the four fields are described below), then use <strong class="text-gray-900 dark:text-white">+ Add Type</strong> for each further one</li>
            <li>Open the <a href="#payment" class="doc-link">Payment</a> tab and choose a payment method and currency</li>
            <li>Save the event</li>
        </ol>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>What to enter</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Price</td>
                        <td>Leave it blank (or enter <code class="doc-inline-code">0</code>) for a free ticket.</td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td>Leave it blank for unlimited. On a recurring event the count is per date.</td>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <td>The name shown to buyers, such as General Admission or VIP. It appears, and is required, as soon as there is more than one ticket type.</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>Optional, supports Markdown.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A <strong class="text-gray-900 dark:text-white">Buy Tickets</strong> button then appears on your event page, or <strong class="text-gray-900 dark:text-white">Get Tickets</strong> when every type is free. Both labels can be reworded under <strong class="text-gray-900 dark:text-white">Customize &rarr; Custom Labels</strong> on the schedule's edit page.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The Tickets mode has five sub-tabs:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">General:</strong> the ticket types themselves, plus passes and per-type sales dates</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="#payment" class="doc-link">Payment</a>:</strong> payment method (Cash, Stripe, Invoice Ninja, Payfast or Payment URL) and the currency</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="#options" class="doc-link">Options</a>:</strong> checkout toggles, custom fields, ticket notes and a terms link</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="#promo-codes" class="doc-link">Promo Codes</a>:</strong> discount codes <x-doc-badge plan="pro" /></li>
            <li><strong class="text-gray-900 dark:text-white"><a href="#add-ons" class="doc-link">Add-ons</a>:</strong> optional extras buyers can attach to an order <x-doc-badge plan="pro" /></li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The Promo Codes and Add-ons tabs still open on the Free plan: they show what the feature does and an upgrade panel in place of the editor.</p>

        <div class="doc-callout">
            <div class="doc-callout-title">Reuse a setup on the next event</div>
            <p>At the bottom of the Tickets section, turn on <strong class="text-gray-900 dark:text-white">Save as default</strong> before saving. The ticket types you just built are then pre-filled on new events for this schedule.</p>
        </div>
    </section>

    <!-- Ticket Types -->
    <section id="ticket-types" class="doc-section">
        <h3 class="doc-subheading">Ticket Types</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A ticket type is a name, a price and a quantity. Add as many as the event needs:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Example</th>
                        <th>Use case</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">General Admission</span></td>
                        <td>Standard entry</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">VIP</span></td>
                        <td>A higher price, with the extras spelled out in the description</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Early Bird</span></td>
                        <td>A cheaper type with a small quantity, or a sales end time</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Student / Senior</span></td>
                        <td>A concession price for a specific group</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Group of 4</span></td>
                        <td>A volume discount that unlocks at four, rather than a separate type</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Season Pass</span></td>
                        <td>A <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass</a> reused across the whole run <x-doc-badge plan="pro" /></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Sold by quantity, and no name-your-price</div>
            <p>A ticket type sells by the number, not by the seat: the buyer takes three, not seats 12, 13 and 14. To sell the seats themselves, see <a href="#allocated-seating" class="doc-link">Allocated Seating</a>. There is also no pay-what-you-wish pricing - every ticket type has one fixed price, and a blank price simply means free.</p>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Ticket Settings</h4>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Quantity</td>
                        <td>How many of this type exist. Blank means unlimited. On a recurring event the count is tracked <strong class="text-gray-900 dark:text-white">per date</strong>, so a quantity of 50 is 50 per occurrence.</td>
                    </tr>
                    <tr>
                        <td>Ticket sales start</td>
                        <td>One absolute date and time at which this type goes on sale. Appears once <strong class="text-gray-900 dark:text-white">Configure sales start/end dates</strong> is on, under <a href="#options" class="doc-link">Options</a>.</td>
                    </tr>
                    <tr>
                        <td>Ticket sales end</td>
                        <td>One absolute date and time at which this type stops selling.</td>
                    </tr>
                    <tr>
                        <td>Max Per Order</td>
                        <td>A cap on how many of this type one order may hold. Added with <strong class="text-gray-900 dark:text-white">+ Add Limit</strong>.</td>
                    </tr>
                    <tr>
                        <td>Volume discount</td>
                        <td>A percentage or fixed amount off once a buyer takes a minimum quantity. Added with <strong class="text-gray-900 dark:text-white">+ Add Discount</strong>.</td>
                    </tr>
                    <tr>
                        <td>Custom Fields (Per Ticket) <x-doc-badge plan="pro" /></td>
                        <td>Questions asked once for every ticket bought, rather than once per order. Added with <strong class="text-gray-900 dark:text-white">+ Add Field</strong>, up to 10 per ticket type.</td>
                    </tr>
                    <tr>
                        <td>Pass or subscription <x-doc-badge plan="pro" /></td>
                        <td>Turns the type into a multi-use pass. See <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a>.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Quantity, sales windows, max per order and volume discounts all work on the Free plan. Only the two rows marked Pro are gated.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Sales windows are fixed instants, not offsets</div>
            <p>A sales start or end is a single date and time, not "two hours before the event". On a recurring event that one instant governs the whole series, so it is best used for a one-off pre-sale window rather than a per-occurrence cutoff. To stop selling at each occurrence automatically, leave the dates blank: sales close at the start time by default, or at the event's end if <strong class="text-gray-900 dark:text-white">Allow sales after event starts</strong> is on.</p>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Individual quantities or one combined pool</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When an event has two or more ticket types and they all carry the <em>same</em> quantity, a choice appears under the list:</p>
        <ul class="doc-list mb-4">
            <li><strong class="text-gray-900 dark:text-white">Individual Quantities</strong> - each ticket type is counted separately, so the capacity is the sum. This is the default.</li>
            <li><strong class="text-gray-900 dark:text-white">Combined Total</strong> - all types draw on a single pool of that size. Use it when 100 means 100 people through the door however they split between General and VIP.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The choice is hidden when the quantities differ, when any quantity is blank, or when there is only one seat-selling type. Passes are ignored in that judgement, since a pass does not define seat capacity.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Volume discount</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose <strong class="text-gray-900 dark:text-white">+ Add Discount</strong> on a ticket type to reward buying in bulk. Set the <strong class="text-gray-900 dark:text-white">minimum quantity</strong> that unlocks it (two or more), then a <strong class="text-gray-900 dark:text-white">percentage</strong> or <strong class="text-gray-900 dark:text-white">fixed amount</strong> off. A group of four booking together gets the discount; a single buyer does not.</p>
        <div class="doc-callout mb-6">
            <div class="doc-callout-title">It applies to that ticket type only</div>
            <p>The discount comes off the line for that ticket type, not the whole order, and never off <a href="#add-ons" class="doc-link">add-ons</a>. Four discounted tickets plus a parking add-on means the four tickets are discounted and the parking is not. A <a href="#promo-codes" class="doc-link">promo code</a> stacks on top: the volume discount is taken off first, and the code is then worked out on what is left, so the two never double-count the same money.</p>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Max per order</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Choose <strong class="text-gray-900 dark:text-white">+ Add Limit</strong> to cap how many of one ticket type a single buyer can take in one order. This is what keeps a two-for-one early bird from being bought out by the first person through the door, and it is separate from the ticket type's total <strong class="text-gray-900 dark:text-white">Quantity</strong>: the quantity is how many exist, the limit is how many one order may hold. Add-ons take the same limit.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Passes &amp; subscriptions <x-doc-badge plan="pro" /></h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">This is a pass or subscription (multi-use)</strong> to make a ticket type one purchase a guest reuses across many events. Four types are offered: a visit pass with a fixed number of visits, an unlimited membership, a festival pass good once per event, and - on a recurring event only - a season pass.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Advance booking is <strong class="text-gray-900 dark:text-white">off by default</strong>, which makes a pass scan-at-the-door only. Turn on <strong class="text-gray-900 dark:text-white">Let holders book seats in advance</strong> first, and the per-date seat cap and the cancellation-deadline settings appear with it. Passes also keep one shared inventory bucket rather than a count per date. See <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> for the full guide.</p>
    </section>

    <!-- Free Tickets -->
    <section id="cart" class="doc-section">
        <h3 class="doc-subheading">Buying Several Events at Once</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A visitor browsing your schedule can collect tickets to more than one event and pay for the lot in a single checkout. On any event with tickets they choose their quantities and select <strong class="text-gray-900 dark:text-white">Add to cart</strong> instead of Checkout, then carry on browsing. A cart button appears in the corner with a running count; opening it lists everything gathered so far, and one Checkout pays for all of it.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Afterwards the buyer lands on a page listing every event they bought, each linking to its own ticket. There is no combined ticket: every event is scanned with its own code, because each door only knows about its own event. The confirmation emails arrive one per event for the same reason.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">What can share a cart</div>
            <p>A single payment cannot be split across payment accounts, currencies or payment rails, so a cart only holds events that agree on all three: the same owner, the same ticket currency, and the same payment method. The cart says so when an event cannot join. Stripe and cash are supported; Invoice Ninja, Payfast and Payment URL are not, since each sends the buyer to a page built for one event.</p>
            <p>Events using individual tickets keep their own checkout. The cart collects one name and email for the whole purchase and has nowhere to put a guest list, so carting one would lose exactly the attendee details that setting exists to collect.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The cart panel shows a running total as events are added, and a gift card can be applied to the whole order at checkout. Prices shown in the panel are for orientation: every ticket is re-read and re-priced from your event when the buyer checks out.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Two dates of the same recurring event count as two entries, so a visitor can take Friday and Saturday in one order. Nothing about the cart is trusted at checkout: every ticket is re-read and re-priced from your event, and if any part of the order can no longer be filled the whole thing is refused rather than charging for a partial order.</p>
    </section>

    <section id="free-events" class="doc-section">
        <h3 class="doc-subheading">Free Tickets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you need multiple ticket types (e.g. General and VIP) or promo codes for a free event, use the <a href="#ticketing" class="doc-link">Tickets</a> mode and set the price to zero:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Select the <strong class="text-gray-900 dark:text-white">Tickets</strong> mode in the Tickets section</li>
            <li>Create a ticket type</li>
            <li>Set the price to <strong class="text-gray-900 dark:text-white">$0</strong> (or leave it blank)</li>
            <li>Set a quantity limit if you have capacity constraints</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors can "purchase" free tickets to RSVP. They'll receive a confirmation email with a QR code, and you'll have a list of who's coming. Add <strong class="text-gray-900 dark:text-white">Ticket Notes</strong> (under Options) to include directions or other instructions in that email. A holder of a free ticket can cancel it themselves from their ticket page, which releases the spot; paid orders are cancelled by you from the <a href="#managing-sales" class="doc-link">Sales</a> page.</p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Zero-price tickets are always sellable</div>
            <p>A ticket type priced at zero never counts toward the Free plan's <a href="#general" class="doc-link">paid-ticket allowance</a>, and it keeps selling even when that allowance is spent. On an event that mixes a $0 tier with paid ones, the free tier stays on sale and only the paid rows pause.</p>
        </div>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Tip</div>
            <p>For simple free events where you only need a headcount, use the <a href="#registration" class="doc-link">Registration</a> mode instead - it's simpler, and its per-date limit and waitlist are free on every plan.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Registration and ticketing are mutually exclusive on a single event. If you need both free and paid options, use the ticketing system with a $0 ticket type alongside your paid tickets.</p>
        </div>
    </section>

    <!-- Payment -->
    <section id="payment" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Payment
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Before you can take money online you need to connect a payment method. Payment methods belong to your account, not to a single event, so you connect one once and pick it per event. Event Schedule supports five options:</p>

        <div class="doc-fields" id="payment-setup">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Stripe</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Accept card payments directly. Money goes straight to your own Stripe account, with no platform fee added on any plan. Set up Stripe in <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Account Settings</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice Ninja</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Invoice Ninja account for invoicing and payment tracking. Choose between <a href="#invoiceninja-modes" class="doc-link">two checkout modes</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment URL</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Use any payment link (PayPal, Venmo, Square, etc.) by entering the URL.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payfast</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Take card, Instant EFT, Capitec Pay and the other South African methods. Settles in rand (ZAR) only. See <a href="#payfast" class="doc-link">Connecting Payfast</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cash</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Accept payment at the door. Add optional payment instructions for attendees. Always available, even with nothing connected.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Connecting Stripe</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect Stripe</strong></li>
            <li>Complete the Stripe onboarding process</li>
            <li>Once connected, Stripe appears as a payment option on the event's <strong class="text-gray-900 dark:text-white">Payment</strong> tab</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Stripe verifies a new account asynchronously, so there is a short window after onboarding where the account is linked but not yet ready to charge. The event editor shows a "verifying" notice during that time. If you finish onboarding in another tab, reload the event page to pick up the change.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">If no payment method is connected</div>
            <p>The Payment tab shows a <strong class="text-gray-900 dark:text-white">Connect Stripe to get paid</strong> panel and the only selectable method is Cash. An event will still save and publish in that state, so connect a method before you announce a paid event.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Recommended</div>
            <p>We recommend using Stripe with Invoice Ninja for the best experience. Invoice Ninja provides additional features like invoicing, payment reminders, and financial reporting.</p>
        </div>

        <h3 id="payfast" class="doc-subheading">Connecting Payfast</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><x-link href="https://payfast.io" target="_blank">Payfast</x-link> is a South African gateway, useful where Stripe is not available. It settles in rand (ZAR) only, so Payfast appears as an option only on events priced in ZAR - and if an event is later switched to another currency, or its method is set through the API, checkout refuses rather than charging the wrong currency. See <a href="#payfast-refused" class="doc-link">When a Payfast checkout is refused</a>.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In Payfast, open <strong class="text-gray-900 dark:text-white">Settings</strong> and note your <strong class="text-gray-900 dark:text-white">Merchant ID</strong> and <strong class="text-gray-900 dark:text-white">Merchant Key</strong></li>
            <li>Set a <strong class="text-gray-900 dark:text-white">passphrase</strong> in the same Payfast screen if you have not already</li>
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong> and open the <strong class="text-gray-900 dark:text-white">Payfast</strong> tab</li>
            <li>Enter all three values and save</li>
            <li>Payfast now appears on the <strong class="text-gray-900 dark:text-white">Payment</strong> tab of any event priced in ZAR</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The passphrase is required rather than optional. It is what lets us verify that a payment notification genuinely came from Payfast, so without one there is no way to tell a real payment from a forged one. Setting it on your Payfast account also makes Payfast reject unsigned checkout requests, which protects your merchant account beyond this integration.</p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Your site may already have an account</div>
            <p>On a selfhosted site, the administrator can configure one Payfast account for everyone. If the Payfast tab says <strong class="text-gray-900 dark:text-white">Provided by this installation</strong>, skip the steps above - Payfast is already available on your ZAR events and payments settle into the site's account. Entering your own details there still works and takes precedence, so you are paid into your own account instead; unlink them to go back. Selfhost administrators: see the <a href="{{ route('marketing.docs.selfhost.stripe') }}#payfast" class="doc-link">Payments guide</a>.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">By default Payfast shows buyers every method your account supports. To send them straight to one instead, tick exactly one entry under <strong class="text-gray-900 dark:text-white">Payment methods</strong>. Ticking several, or none, leaves the choice to Payfast.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Testing with the sandbox</div>
            <p>Turn on <strong class="text-gray-900 dark:text-white">Test mode</strong> to send payments to Payfast's sandbox instead of taking real money. Payfast's public sandbox credentials are merchant ID <code>10000100</code> and merchant key <code>46f0cd694581a</code>. You still need a passphrase: set one in your <x-link href="https://sandbox.payfast.co.za" target="_blank">Payfast sandbox account</x-link> and enter it here alongside them, because all three are required whether or not test mode is on. Note that Payfast cannot reach a notification URL on <code>localhost</code>, so a sandbox purchase only completes end to end on a publicly reachable install. While test mode is on, the payment page shows buyers a clear test-mode notice, and the payment method appears with a test-mode label on the event form. Turn test mode off before you sell real tickets. Selfhosted installs need no extra configuration for the notification to be accepted - it is authenticated by its signature and by asking Payfast to confirm it, not by the address it arrives from, so running behind Cloudflare, a reverse proxy or Docker changes nothing.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title" id="payfast-refused">When a Payfast checkout is refused</div>
            <p>Two orders never reach Payfast, because it would reject them on its own page after the seats were already held. An order under <strong class="text-gray-900 dark:text-white">R5.00</strong> - Payfast's minimum - and any event whose currency is not ZAR. In both cases the buyer is returned to the ticket page with a message, and the seats go straight back on sale. If you see that on your own event, check the event's <strong class="text-gray-900 dark:text-white">Currency</strong> on the Payment tab: an event can keep Payfast selected after its currency is changed, and it then shows in the dropdown marked <em>no longer available</em> until you pick something else.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Refunds</div>
            <p>Refunds are issued from your own Payfast dashboard. Marking a sale refunded here records it without moving money, which is the same as every other payment method.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">What Payfast does not do</div>
            <p>A Payfast event cannot be combined with others in the <a href="#cart" class="doc-link">multi-event cart</a> - a Payfast payment covers one event - and it cannot offer <a href="#installments" class="doc-link">monthly installments</a>, which need a card the gateway can charge again later. <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift cards</a> cannot be sold through Payfast either. Everything else - promo codes, add-ons, volume discounts, per-attendee tickets - works normally.</p>
        </div>
    </section>

    <!-- Invoice Ninja Modes -->
    <section id="invoiceninja-modes" class="doc-section">
        <h3 class="doc-subheading">Invoice Ninja Modes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When using Invoice Ninja, choose between two checkout modes in your <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">payment settings</a>.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice Mode</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ticket selection and promo codes are handled in Event Schedule. An invoice is created in Invoice Ninja for each purchase. Supports multiple promo codes and per-ticket promo targeting. Buyers can optionally create an Event Schedule account during checkout.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Link Mode</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Buyers select tickets and enter promo codes on the Invoice Ninja purchase page. Invoices are grouped in Invoice Ninja, making bulk management easier. Supports one promo code per event (applied to all tickets). Buyers can optionally create an Event Schedule account during checkout. See the <x-link href="https://invoiceninja.github.io/docs/user-guide/subscriptions" target="_blank">Invoice Ninja payment link docs</x-link> for more details.</p>
            </div>
        </div>

        <div class="doc-table-wrap">
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
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            Options
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Configure additional checkout settings for your event's tickets.</p>

        <h3 id="checkout-fields" class="doc-subheading">Custom Checkout Fields <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Collect additional information from attendees during checkout. You can add up to 10 custom fields per event. Each field has a name, a type (single line, paragraph, switch, date, dropdown or multi-select) and a required flag, and fields can be dragged into the order you want them asked.</p>

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

        <p class="text-gray-600 dark:text-gray-300 mb-6">Responses are stored with each sale, shown when you expand the sale on the <a href="#managing-sales" class="doc-link">Sales</a> page, and included in the <a href="#export" class="doc-link">CSV export</a> as one column per field.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Per-Ticket Custom Fields</div>
            <p>Fields added here are asked once per order. To ask something once per ticket instead, add the field on the ticket type itself: on the <strong>General</strong> tab, choose <strong>+ Add Field</strong> under a ticket type. Per-ticket fields are useful when each attendee needs to answer individually (meal choice, name for a badge), and each ticket type takes up to 10 of them.</p>
        </div>

        <h3 class="doc-subheading">Additional Settings</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What it does</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ask for phone number</td>
                        <td>Adds a phone field to the checkout form. Two sub-options appear once it is on: <strong class="text-gray-900 dark:text-white">Required</strong> and a <strong class="text-gray-900 dark:text-white">Country code</strong> selector. The number is stored on the sale, shown in the Sales list and included in the <a href="#export" class="doc-link">CSV export</a>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Individual tickets</td>
                        <td>Each attendee gets their own confirmation email and QR code instead of one per order. A sub-toggle, <strong class="text-gray-900 dark:text-white">Collect ticket fields per guest</strong>, then asks the per-ticket custom fields once per attendee.</td>
                        <td>Pro for ticketed events; free on <a href="#registration" class="doc-link">Registration</a></td>
                    </tr>
                    <tr>
                        <td>Allow sales after event starts</td>
                        <td>Keeps selling until the event ends (start time plus duration) instead of stopping at the start time.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Configure sales start/end dates</td>
                        <td>Reveals the per-ticket-type sales start and end fields described under <a href="#ticket-types" class="doc-link">Ticket Types</a>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Show unavailable tickets</td>
                        <td>Displays sold out and expired ticket types to visitors in a disabled state, so they can see what was offered.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Expire unpaid tickets</td>
                        <td>Releases unpaid reservations after a set number of hours, returning them to stock. Only appears when at least one ticket type has both a price and a limited quantity, since there is nothing to release otherwise.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Ticket Notes</td>
                        <td>Text included in the confirmation email and printed on the attendee's ticket (directions, parking, dress code, what to bring). Supports <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">template variables</a> such as <code class="doc-inline-code">{event_name}</code> and <code class="doc-inline-code">{venue}</code>. On a Registration event the same field is labelled <strong class="text-gray-900 dark:text-white">Registration Notes</strong>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Terms URL</td>
                        <td>Links to your terms and conditions. Buyers must agree before purchasing. Leave it blank to use the default terms.</td>
                        <td>Free</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Installments -->
    <section id="installments" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
            </svg>
            Installment Payments <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Let buyers spread the cost of an expensive ticket over monthly payments. Useful for courses, retreats and multi-day events announced well in advance: a buyer pays the first installment at checkout and gets their ticket straight away, and the rest is charged automatically to the same card each month.</p>

        <h3 class="doc-subheading">Setting it up</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open your event, go to <strong class="text-gray-900 dark:text-white">Tickets</strong> then the <strong class="text-gray-900 dark:text-white">Payment</strong> tab, and turn on <strong class="text-gray-900 dark:text-white">Let buyers pay in monthly installments</strong>. The option appears only when the event is <a href="#payment" class="doc-link">paid through Stripe</a>, because Stripe is the only payment method that can charge a saved card automatically.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr><th>Setting</th><th>What it does</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Number of payments</td>
                        <td>How many monthly payments the order total is split into. The first is taken at checkout. Amounts that do not divide evenly put the odd cent on the first payment, so 1,000 over three is 333.34 then 333.33 twice.</td>
                    </tr>
                    <tr>
                        <td>Last payment due before the event</td>
                        <td>How much runway you want to chase a failed payment before the doors open. We recommend at least 14 days. The editor shows you live whether the schedule you have chosen actually finishes in time, and warns you if it would not.</td>
                    </tr>
                    <tr>
                        <td>Only offer installments on orders over</td>
                        <td>Optional. Keeps the option off small orders, so you can offer it on a full course but not a single tasting. Leave it blank to offer it on every order.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every individual payment has to clear Stripe's minimum charge of roughly $0.50, so splitting a small order too many ways withdraws the option. A <a href="#promo-codes" class="doc-link">promo code</a> or gift card applied at checkout can take an order under that line, or under your own minimum, and the buyer will simply not be offered monthly payments. Installments are also not offered for a basket spanning several events, or for one containing a <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass</a>.</p>

        <h3 id="installments-buyer" class="doc-subheading">What the buyer sees</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">At checkout the buyer chooses between paying in full and paying monthly. Paying in full is selected by default. If they choose monthly they see every payment date and amount before committing, confirm that they authorise the future charges, and are charged only the first payment. There is no interest and no fee: the total is the same either way.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Their ticket is valid from the first payment. Two days before each following payment we email them a reminder naming the card and the amount. Every one of those emails links to their own payment plan page, where they can pay early, clear the whole balance or change their card. That page is the only place their saved card is shown: the ticket page is what the QR code opens, and door staff scan it.</p>

        <h3 id="installments-tracking" class="doc-subheading">Tracking payments</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Installments</strong> tab on your <a href="{{ route('marketing.docs.tickets') }}#managing-sales" class="doc-link">Sales page</a> lists everyone paying monthly, how far through they are, what has been collected, what is outstanding, and what to expect month by month. Overdue plans sort to the top.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You get one daily summary of the payments due in the next couple of days, rather than an email per buyer, and an immediate email whenever a payment fails.</p>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Your sales figures will run ahead of your bank balance</div>
            <p>Sales totals count the full ticket price at the moment of purchase, because the ticket is issued then. So your Sales and <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> figures include money you have not collected yet while plans are still running. The Installments tab is the one that shows what has genuinely been taken.</p>
        </div>

        <h3 id="installments-missed" class="doc-subheading">When a payment fails</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If a card is declined we retry it three more times over the following nine days, emailing the buyer each time and telling them plainly that their ticket is still valid. If their bank asks them to confirm the payment (common in Europe) we send a different email asking them to approve it, and do not count it as a decline.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If the balance is still unpaid after that, the ticket goes <strong class="text-gray-900 dark:text-white">on hold</strong>: it stops scanning at the door until they pay, and paying makes it valid again immediately. A week before the event everyone with an outstanding balance gets a final notice, and you get a list of them, so nobody is surprised at the door.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Running out of retries is the common route to a hold, but not the only one. A plan also goes on hold if a bank authentication request goes unanswered for a week, or if your own Stripe connection is disconnected so nothing can be collected at all. That second one is worth knowing: the buyer has done nothing wrong and cannot fix it, so you are the one we email, and reconnecting Stripe is what restarts collection. For the ordinary routes, the buyer replacing or re-confirming their card lifts the hold and puts the remaining payments back on schedule.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One state deliberately waits for a person: if a charge is interrupted and we cannot tell whether the money moved, we stop rather than retry, because retrying a payment that may already have succeeded is how a buyer gets charged twice. The same applies to a payment that arrives but does not match anything we can apply it to. Both show on the Installments tab as needing your attention, with the Stripe reference to check against your dashboard, and neither is resolved by the buyer changing their card.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Scanning a ticket that is on hold shows your door staff the attendee's name and the amount outstanding rather than a flat rejection, so they can take payment or let the guest in at your discretion.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Cancelling or refunding an order, cancelling the event, or deleting the schedule all stop the remaining payments immediately. Refunding the money already collected is done from your own Stripe dashboard; the Installments tab lists every payment reference so you can find them.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted installs</div>
            <p>Installments are available on every schedule, and payments settle through your own Stripe keys rather than a connected account. They do depend on the scheduler: the first payment is taken at checkout either way, but nothing charges the second and later payments unless <code class="doc-inline-code">schedule:run</code> is running on a cron. See <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">selfhosting</a> for setting that up.</p>
        </div>
    </section>

    <!-- Promo Codes -->
    <section id="promo-codes" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            Promo Codes <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Offer discounts to attendees with promo codes. Buyers enter a code during checkout to receive a discount on their purchase.</p>

        <h3 class="doc-subheading">Adding a Promo Code</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event</li>
            <li>Go to the <strong class="text-gray-900 dark:text-white">Tickets &rarr; Promo Codes</strong> tab</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add Promo Code</strong></li>
            <li>Enter the code (e.g., "EARLYBIRD", "VIP50")</li>
            <li>Choose the discount type and value</li>
            <li>Save the event</li>
        </ol>

        <h3 class="doc-subheading">Discount Types</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Percentage:</strong> A percentage off the ticket price (e.g., 20% off)</li>
            <li><strong class="text-gray-900 dark:text-white">Fixed amount:</strong> A flat amount off the ticket price (e.g., $10 off)</li>
        </ul>

        <h3 class="doc-subheading">Promo Code Settings</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Max Uses:</strong> Limit how many times the code can be used (leave blank for unlimited). The number of times it has been used is shown beside the code.</li>
            <li><strong class="text-gray-900 dark:text-white">Expires At:</strong> A date and time when the code stops working</li>
            <li><strong class="text-gray-900 dark:text-white">Active:</strong> A toggle that switches the code off without deleting it</li>
            <li><strong class="text-gray-900 dark:text-white">Applies To:</strong> <strong class="text-gray-900 dark:text-white">All Tickets</strong>, or <strong class="text-gray-900 dark:text-white">Specific Tickets</strong> to tick the types it covers</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Each promo code has a copy-link button that produces a shareable URL pre-filling the code at checkout, making it easy to distribute to your audience.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A promo code never discounts <a href="#add-ons" class="doc-link">add-ons</a>, and it is worked out after any <a href="#ticket-types" class="doc-link">volume discount</a> on the same line, so the two never double-count the same money. A percentage code is capped at 100% and a fixed code can never discount more than the eligible subtotal.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Invoice Ninja Payment Link Mode</div>
            <p>When using Invoice Ninja in payment link mode, only one promo code per event is supported and it applies to all ticket types. Use invoice mode for multiple promo codes with per-ticket targeting.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Gift cards are separate</div>
            <p>Checkout also accepts a gift card code, which spends a prepaid balance instead of applying a discount. A buyer can use a promo code and a gift card on the same order. See <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift Cards</a>.</p>
        </div>
    </section>

    <!-- Add-ons -->
    <section id="add-ons" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.491 48.491 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
            </svg>
            Add-ons <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add-ons are optional purchasable items that customers can include with their ticket order, such as parking passes, merchandise, or meal packages.</p>

        <h3 class="doc-subheading">Creating an Add-on</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event</li>
            <li>Go to the <strong class="text-gray-900 dark:text-white">Tickets &rarr; Add-ons</strong> tab</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add add-on</strong></li>
            <li>Fill in the add-on details and save the event</li>
        </ol>

        <h3 class="doc-subheading">Add-on Fields</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Name</strong> (required): The name displayed to customers (e.g., "Parking Pass", "Event T-Shirt")</li>
            <li><strong class="text-gray-900 dark:text-white">Price:</strong> The price per unit (leave blank or set to 0 for free add-ons)</li>
            <li><strong class="text-gray-900 dark:text-white">Quantity:</strong> The total number available (leave blank for unlimited)</li>
            <li><strong class="text-gray-900 dark:text-white">Description:</strong> An optional description with additional details</li>
            <li><strong class="text-gray-900 dark:text-white">URL:</strong> An optional link, for a size chart or a product page</li>
            <li><strong class="text-gray-900 dark:text-white">Image:</strong> An optional picture of the item</li>
            <li><strong class="text-gray-900 dark:text-white">+ Add Limit:</strong> Caps how many of this add-on one order may hold, the same as <a href="#ticket-types" class="doc-link">Max Per Order</a> on a ticket type</li>
        </ul>

        <h3 class="doc-subheading">How Add-ons Work</h3>
        <ul class="doc-list mb-6">
            <li>Add-ons appear in the checkout form only after the customer selects at least one ticket</li>
            <li>Customers choose a quantity for each add-on (or leave it at 0 to skip)</li>
            <li>Add-on totals are added to the ticket total at checkout</li>
            <li>Promo codes and volume discounts do not apply to add-ons</li>
            <li>Add-ons are tracked separately in sales records, the CSV export and confirmation emails</li>
            <li>Add-ons never count toward the Free plan's <a href="#general" class="doc-link">paid-ticket allowance</a></li>
        </ul>
    </section>

    <!-- Managing Sales -->
    <!-- Allocated Seating - now its own page; the anchor stays alive for existing links -->
    <section id="allocated-seating" class="doc-section">
        <h2 class="doc-heading">Allocated Seating <x-doc-badge plan="enterprise" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything above sells by the number. If your venue has rows, you can sell the seats themselves instead: draw the room once as a seating plan, attach it to an event, and buyers pick where they sit. Your box office gets the same map to hold seats back, take a booking over the phone, move somebody or release a single seat.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">It has a guide of its own: <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">Allocated Seating</a>.</p>
    </section>

    <section id="managing-sales" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
            Managing Sales
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Track and manage your ticket sales from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong>. The page spans every schedule you own or administer, and carries tabs for <strong class="text-gray-900 dark:text-white">Sales</strong>, <strong class="text-gray-900 dark:text-white">Waitlist</strong>, <strong class="text-gray-900 dark:text-white">Feedback</strong>, <strong class="text-gray-900 dark:text-white">Subscriptions</strong>, <strong class="text-gray-900 dark:text-white"><a href="#installments" class="doc-link">Installments</a></strong> and <strong class="text-gray-900 dark:text-white">Gift Cards</strong>.</p>

        <h3 class="doc-subheading">What You Can See</h3>
        <ul class="doc-list mb-6">
            <li>Every purchase with the buyer's name, email and phone</li>
            <li>The event and the occurrence date the sale is for</li>
            <li>Payment status: paid, unpaid, cancelled or refunded</li>
            <li>The amount, any discount or gift card applied, and the transaction reference</li>
            <li>Check-in status, and the star rating if the buyer left <a href="#feedback" class="doc-link">feedback</a></li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Expand a row to see the individual tickets in the order, the add-ons, and any custom field answers. Columns can be sorted by clicking their headers.</p>

        <h3 id="filtering-sales" class="doc-subheading">Filtering Sales</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use the filter input at the top of the sales list to search by buyer name, email, phone, event name, status or transaction reference. The filter updates results in real time. When exporting sales data, only the currently filtered results are included in the export.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Past events are hidden by default.</strong> Turn on <strong class="text-gray-900 dark:text-white">Include past events</strong> to bring older sales back into the list, and into the export.</p>

        <h3 class="doc-subheading">Actions</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open the <strong class="text-gray-900 dark:text-white">Actions</strong> menu on a sale row:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">View Ticket:</strong> Open the attendee's ticket page, with its QR code</li>
            <li><strong class="text-gray-900 dark:text-white">Send Email:</strong> Send the confirmation email again</li>
            <li><strong class="text-gray-900 dark:text-white">Mark Paid:</strong> For cash or other payments taken outside the app</li>
            <li><strong class="text-gray-900 dark:text-white">Refund Ticket:</strong> Mark a paid sale as refunded</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel Ticket:</strong> Cancel a paid or unpaid sale without recording a refund</li>
            <li><strong class="text-gray-900 dark:text-white">Delete:</strong> Permanently remove a sale record</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Refunding, cancelling or deleting a sale returns its tickets to stock, gives back any promo code use, credits any gift card balance the buyer spent, and notifies the next person on the <a href="#waitlist" class="doc-link">waitlist</a>.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Refunds move no money</div>
            <p>Refund Ticket changes the status in Event Schedule and adjusts your revenue figures. The actual money is returned in your payment provider's own dashboard - Stripe, Invoice Ninja or Payfast. Cancelling or deleting a sale that was paid for shows you a reminder to handle the refund yourself. These actions fire the matching <x-link href="{{ route('marketing.docs.developer.webhooks') }}">webhook</x-link>: <code class="doc-inline-code">sale.paid</code>, <code class="doc-inline-code">sale.refunded</code> or <code class="doc-inline-code">sale.cancelled</code>.</p>
        </div>
    </section>

    <!-- Sale Notifications -->
    <section id="sale-notifications" class="doc-section">
        <h3 class="doc-subheading">Sale Notification Emails <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Opt in to receive an email notification every time a ticket sells. Each notification includes:</p>

        <ul class="doc-list mb-6">
            <li>Buyer name and email</li>
            <li>Ticket type and quantity</li>
            <li>Total amount</li>
            <li>Payment status</li>
            <li>Discount or promo code applied</li>
        </ul>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">How to Enable</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Open the schedule's edit page in the admin panel, go to <strong class="text-gray-900 dark:text-white">Settings &rarr; Notifications</strong> and turn on <strong class="text-gray-900 dark:text-white">New ticket sale</strong>. Every editor of the schedule can opt in separately. If <a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">push notifications</a> are enabled, the same alert is mirrored to the browser.</p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">The first sale always notifies</div>
            <p>Ongoing sale notifications are a Pro feature, but the <strong class="text-gray-900 dark:text-white">first paid sale on each event</strong> emails you on every plan, including Free. You never have to poll the Sales page to find out that your first ticket sold.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Sender &amp; Compliance</div>
            <p>If you have a custom sender email configured for your schedule, sale notifications are sent from that address. All notification emails include an unsubscribe link for compliance.</p>
        </div>
    </section>

    <!-- Export -->
    <section id="export" class="doc-section">
        <h3 class="doc-subheading">Exporting Sales Data <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Export your sales data for accounting, tax purposes, or to import into other systems. The export covers every schedule you own or administer.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong></li>
            <li>Narrow the list with the filter box, and turn on <strong class="text-gray-900 dark:text-white">Include past events</strong> if you need older sales. The export contains exactly what the list is showing.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Export</strong></li>
            <li>Download your sales data as a CSV file</li>
        </ol>

        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Export Includes</h4>
            <ul class="doc-list text-sm">
                <li>Buyer name, email and phone</li>
                <li>Event, event date and purchase date</li>
                <li>Ticket types and quantities, and add-ons, as separate columns</li>
                <li>Amount and currency</li>
                <li>Promo code and discount amount, gift card code and gift card amount</li>
                <li>Transaction reference, payment method and status</li>
                <li>Check-in status and check-in time</li>
                <li>Pass type, visits used and expiry, for <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass</a> sales</li>
                <li>Seats, for <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated seating</a> sales</li>
                <li>Custom checkout field responses (event-level and ticket-level), one column per field</li>
            </ul>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>The CSV includes a byte order mark (BOM) for Excel compatibility. Export your sales data regularly for your records - this is especially useful for tax reporting and financial reconciliation.</p>
        </div>
    </section>

    <!-- Importing Attendees -->
    <section id="importing-attendees" class="doc-section">
        <h3 class="doc-subheading">Importing Attendees <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Bulk-add attendees who paid out-of-band (cash, sponsored, or through a third-party system) instead of checking them out through the public ticket page. Up to 5,000 attendees per import.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and click <strong class="text-gray-900 dark:text-white">Import</strong></li>
            <li>Pick a schedule (if you own more than one), an event, and the <strong class="text-gray-900 dark:text-white">Event Date</strong> the attendees are coming on</li>
            <li>Either type rows on the <strong class="text-gray-900 dark:text-white">Form Entry</strong> tab or switch to <strong class="text-gray-900 dark:text-white">Upload CSV</strong></li>
            <li>When uploading, map each CSV column to a field (name, email, phone, ticket type, etc.), then click <strong class="text-gray-900 dark:text-white">Next</strong> to review</li>
            <li>Optionally toggle <strong class="text-gray-900 dark:text-white">Send Email</strong> to send a confirmation email to each attendee</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save Attendees</strong></li>
        </ol>

        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Supported CSV columns</h4>
            <ul class="doc-list text-sm">
                <li>Name, Email (required), Phone</li>
                <li>Ticket Type (matched by name to existing ticket types)</li>
                <li>Quantity, Amount, Status (paid / unpaid)</li>
                <li>Any event-level or ticket-level custom fields you've defined</li>
            </ul>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Tips</div>
            <p>Email is the only required column - everything else auto-detects from the header name, and rows with no ticket type fall back to the type you picked. Comma, semicolon, and tab delimiters are all supported, as are UTF-8 CSVs exported from Excel. Duplicate emails within the same import are skipped automatically, as is any row that would push a ticket type past its remaining quantity; the result screen lists which rows were skipped and why.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Imports never spend the allowance</div>
            <p>Imported attendees are recorded with their own payment method, so they never count toward the Free plan's <a href="#general" class="doc-link">paid-ticket allowance</a>. Sending the confirmation emails needs a working sender address for the schedule.</p>
        </div>
    </section>

    <!-- Check-in -->
    <section id="check-in" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
            </svg>
            Check-in at the Door
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Use your phone to scan tickets at the door. No special hardware needed.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> on your phone</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Scan Ticket</strong></li>
            <li>Point your camera at the QR code on the ticket</li>
            <li>The app shows the ticket details and marks it as checked in</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Scanning is free; the live dashboard is Pro</div>
            <p>Confirmation emails and ticket pages show a QR code on every plan, including Free, and scanning it in the app is free too, for every ticket and registration you sell. The live <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a> is the part that needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install has both.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Multiple Scanners</div>
            <p>Any team member with access to your schedule can scan tickets, including viewers. Just have them log in on their phone.</p>
        </div>

        <h3 class="doc-subheading">Ticket Security</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each ticket has a unique QR code that can only be scanned once. If someone tries to use a ticket that's already been checked in, you'll see a warning. An unpaid or cancelled order shows its QR code struck through, marked Unpaid or Void.</p>
    </section>

    <!-- Check-in Dashboard -->
    <section id="checkin-dashboard" class="doc-section">
        <h3 class="doc-subheading">Check-in Dashboard <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Monitor attendance in real time from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales &rarr; Check-in</strong>. The dashboard provides a live overview of check-in progress for your event.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Overall progress bar</strong> with percentage of attendees checked in</li>
            <li><strong class="text-gray-900 dark:text-white">Per-ticket-type breakdown</strong> showing check-in counts for each ticket type</li>
            <li><strong class="text-gray-900 dark:text-white">Guest headcount</strong> - when a <a href="{{ route('marketing.docs.subscriptions') }}#admissions-per-event" class="doc-link">pass admits guests</a>, a headcount including guests is shown next to the check-in count</li>
            <li><strong class="text-gray-900 dark:text-white">Reserved pass seats</strong> for the occurrence, so door staff know how many pass holders are still expected</li>
            <li><strong class="text-gray-900 dark:text-white">Recent activity feed</strong> showing the last 10 check-ins with attendee names and times, and their seat on an <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated</a> event</li>
            <li><strong class="text-gray-900 dark:text-white">Filter by event and event date</strong> to view specific event dates</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Counts are keyed to the venue's own calendar date, so an evening event west of UTC reports correctly rather than rolling over at the wrong midnight. Only redemptions count as checked in: a pass holder who booked a seat in advance appears in the reserved count until they actually arrive.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Auto-Refresh</div>
            <p>The dashboard refreshes every 10 seconds while the tab is in the foreground, so you always see the latest check-in data without draining a phone in your pocket. It works on any device, including phones and tablets.</p>
        </div>
    </section>

    <!-- Waitlist -->
    <section id="waitlist" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Waitlist
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When an event date fills up, fans can join a waitlist to be notified when spots become available.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>When all tickets sell out for an event date, a <strong class="text-gray-900 dark:text-white">Join Waitlist</strong> button appears on the event page</li>
            <li>Guests enter their name and email</li>
            <li>When a spot opens up (a sale is cancelled, refunded, or expires unpaid), the next person in line is notified by email</li>
            <li>They receive a link that is valid for 24 hours</li>
            <li>If they don't purchase in time, the next person in line is notified</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Free for registration, Pro for tickets</div>
            <p>The waitlist on a full <a href="#registration" class="doc-link">Registration</a> date works on every plan, including Free. The waitlist on a sold-out <em>ticketed</em> event needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above.</p>
        </div>

        <h3 class="doc-subheading">Managing the Waitlist</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">View and manage the waitlist on the <strong class="text-gray-900 dark:text-white">Waitlist</strong> tab of <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong>. The tab appears once there is at least one entry, and the table shows each entry's name, email, event, date, and status.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One at a Time</div>
            <p>Only one person is notified at a time to prevent overselling. The next person is notified only after the current person's 24-hour window expires or they complete their purchase.</p>
        </div>
    </section>

    <!-- Post-Event Feedback -->
    <section id="feedback" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            Post-Event Feedback <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Automatically collect ratings and comments from attendees after your events end. Feedback emails are sent to ticket buyers and RSVP attendees, linking to a simple form where they can rate their experience.</p>

        <h3 class="doc-subheading">Enabling Feedback</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to your schedule's edit page in the admin panel</li>
            <li>Open <strong class="text-gray-900 dark:text-white">Engagement &rarr; Feedback</strong></li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">Post-event feedback</strong></li>
            <li>Choose a delay (how long after the event ends before emails are sent). The default is 24 hours.</li>
            <li>Save your changes</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Needs a sender address</div>
            <p>On eventschedule.com the toggle stays disabled until the schedule has its own <a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">email settings</a> configured, since feedback requests are sent from your address rather than ours. A selfhosted install only needs a working mailer.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Per-Event Override</div>
            <p>You can override the schedule-level setting for individual events. In the event edit page, open <strong class="text-gray-900 dark:text-white">Engagement &rarr; Feedback</strong> and choose "Enabled" or "Disabled" to override, or "Use schedule default" to follow the schedule setting.</p>
        </div>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>After an event ends and the configured delay passes, feedback request emails are automatically sent to attendees</li>
            <li>Each email contains a link to a feedback form branded with your schedule's logo and colors</li>
            <li>Attendees rate their experience from 1 to 5 stars and can leave an optional comment</li>
            <li>Each attendee can only submit feedback once</li>
        </ol>

        <h3 class="doc-subheading">Viewing Feedback</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">View all feedback from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales &rarr; Feedback</strong> tab. The page shows:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Summary card</strong> with average rating, total responses, and response rate</li>
            <li><strong class="text-gray-900 dark:text-white">Feedback table</strong> listing each response with attendee name, event, date, star rating, comment, and submission time</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The tab also shows a <strong class="text-gray-900 dark:text-white">Sent - Awaiting Response</strong> list of attendees who were emailed but have not replied yet. If a request was missed or landed in spam, click <strong class="text-gray-900 dark:text-white">Resend</strong> next to an attendee to send the feedback request again.</p>

        <h3 class="doc-subheading">Exporting Feedback</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Click <strong class="text-gray-900 dark:text-white">Export Feedback</strong> on the Feedback tab to download a CSV file with all feedback data.</p>

        <h3 class="doc-subheading">Feedback Notifications</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">To receive an email when new feedback is submitted, enable <strong class="text-gray-900 dark:text-white">New feedback</strong> in <strong class="text-gray-900 dark:text-white">Settings &rarr; Notifications</strong>. Each notification includes the event name, attendee name, star rating, and comment.</p>
    </section>

    <!-- Financial Information -->
    <section id="financial" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Financial Information
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Important information about payments, refunds, and taxes.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Refunds</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Refunds are handled through your payment provider (Stripe, Invoice Ninja or Payfast). Event Schedule marks the sale as cancelled, but you must process the actual refund in that provider's dashboard. A Payfast reference is shown as plain text rather than a link, so you will need to search for it in your Payfast dashboard. Stripe refunds appear on customer statements within 5-10 business days.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Taxes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Event Schedule does not automatically calculate or collect sales tax. Set your ticket prices inclusive of any applicable taxes. For tax reporting, export your sales data from the Sales page. Consult a tax professional for your specific obligations.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Processing Fees</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stripe charges their standard processing fees (typically 2.9% + $0.30 per transaction in the US). These fees are deducted from your payouts. Event Schedule adds no platform fee on any plan, Free included.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled or Deleted Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If you delete an event with sold tickets, ticket holders are <strong class="text-gray-900 dark:text-white">not</strong> automatically notified or refunded. Before deleting, you should: (1) contact ticket holders about the cancellation, (2) process refunds through your payment provider, and (3) then delete the event. Sales data is preserved even after event deletion.</p>
            </div>
            <div class="doc-field">
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
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Embed Widget
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Embed a ticket purchase or RSVP form directly on your own website using an iframe. Visitors can buy tickets or register without leaving your site.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">RSVP embed is free; the ticket embed is Pro</div>
            <p>The <code class="doc-inline-code">rsvp=true</code> widget works on every plan, including Free, and always has. The <code class="doc-inline-code">tickets=true</code> widget needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above.</p>
        </div>

        <h3 class="doc-subheading">Getting the Embed Code</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your event in the admin panel and go to the <strong class="text-gray-900 dark:text-white">Tickets</strong> section</li>
            <li>Enable <strong class="text-gray-900 dark:text-white">Tickets</strong> or <strong class="text-gray-900 dark:text-white">Registration</strong> mode and save the event</li>
            <li>Click the <strong class="text-gray-900 dark:text-white">Embed Tickets</strong> (or <strong class="text-gray-900 dark:text-white">Embed Registration</strong>) link next to the section heading</li>
            <li>Copy the iframe code and paste it into your website's HTML</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The link that opens the snippet only appears for Pro schedules. On the Free plan you can still embed the RSVP form by building the URL yourself from the parameters below.</p>

        <h3 class="doc-subheading">URL Parameters</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You can customize the embed URL with these parameters:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">tickets=true</code></td>
                        <td>Show the ticket purchase form <x-doc-badge plan="pro" /></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">rsvp=true</code></td>
                        <td>Show the RSVP registration form <x-doc-badge plan="free" /></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">embed=true</code></td>
                        <td>Enable embed mode (compact layout, no navigation)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">dark=true</code></td>
                        <td>Force dark mode</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">promo=CODE</code></td>
                        <td>Pre-fill a promo code</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">lang=xx</code></td>
                        <td>Set the widget language (e.g., <code class="doc-inline-code">lang=es</code> for Spanish)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Private and password-protected events</div>
            <p>The embed widget is not available for private events, since they require authentication to access. A password-protected event only embeds for someone who has already entered the password.</p>
        </div>

        <div class="doc-callout doc-callout-info mt-4">
            <div class="doc-callout-title">Payment Redirects</div>
            <p>Stripe, Invoice Ninja, and custom payment URL checkouts will open in the parent window (outside the iframe) since external payment portals may not support being loaded inside iframes. Cash and free ticket checkouts complete inside the embed.</p>
        </div>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events to sell tickets for</li>
            <li><a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift Cards</a> - Sell prepaid gift cards redeemable at checkout</li>
            <li><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> - Sell one pass reused across many events</li>
            <li><a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointments</a> - Take bookings for a time slot, with their own allowance</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Promote your events</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Create promotional images</li>
            <li><a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> - Track conversion rates and revenue per view</li>
            <li><a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">Account Settings</a> - Set up your payment method</li>
            <li><a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">Newsletters</a> - Send newsletters to promote ticket sales</li>
            <li><a href="{{ marketing_url('/features/embed-tickets') }}" class="doc-link">Embed Tickets</a> - Embed a ticket form on your website</li>
        </ul>
    </section>


    <x-slot:schema>
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
                    "text": "Go to Admin Panel, then Settings, then Payment Methods, and click Connect Stripe. Complete the Stripe onboarding process.",
                    "url": "{{ url(route('marketing.docs.tickets')) }}#payment-setup"
                },
                {
                    "@type": "HowToStep",
                    "name": "Create Ticket Types",
                    "text": "Edit your event, scroll to the Tickets section, select the Tickets mode, and add a type with a price, quantity and description.",
                    "url": "{{ url(route('marketing.docs.tickets')) }}#ticket-types"
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
                    "text": "Use the real-time check-in dashboard at Admin Panel, then Sales, then Check-in, to monitor attendance with progress bars and a live activity feed.",
                    "url": "{{ url(route('marketing.docs.tickets')) }}#checkin-dashboard"
                },
                {
                    "@type": "HowToStep",
                    "name": "Check In Attendees",
                    "text": "Go to Admin Panel, then Sales on your phone, click Scan Ticket, and point your camera at the QR code.",
                    "url": "{{ url(route('marketing.docs.tickets')) }}#check-in"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
