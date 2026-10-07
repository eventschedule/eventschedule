<x-docs-page
    key="tickets"
    title="Sell Tickets: Payments, Refunds, Check-In - Event Schedule"
    description="Sell tickets or run free registration: connect Stripe or PayPal, build ticket types, refund from the Sales page and scan QR codes at the door."
    lede="Free registration on every plan, paid ticketing on Pro, and zero platform fees either way. Connect payment processing and create ticket types; only your processor's fee comes off a paid ticket."
    article-description="How to sell tickets and run free registration: payment methods, ticket types, refunds, check-in at the door and the interest list."
>
    <x-slot:toc>
        <x-doc-nav-group label="General" href="#general" expanded>
            <x-doc-nav-link href="#registration">Free Registration</x-doc-nav-link>
            <x-doc-nav-link href="#ticketing">Sell Tickets</x-doc-nav-link>
            <x-doc-nav-link href="#ticket-types">Ticket Types</x-doc-nav-link>
            <x-doc-nav-link href="#free-events">Free Tickets</x-doc-nav-link>
            <x-doc-nav-link href="#external">Tickets Elsewhere</x-doc-nav-link>
            <x-doc-nav-link href="#cart">Multi-Event Cart</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Payment" href="#payment">
            <x-doc-nav-link href="#payment-row">The Payment Row</x-doc-nav-link>
            <x-doc-nav-link href="#stripe">Stripe</x-doc-nav-link>
            <x-doc-nav-link href="#invoiceninja-modes">Invoice Ninja Modes</x-doc-nav-link>
            <x-doc-nav-link href="#payfast">Payfast</x-doc-nav-link>
            <x-doc-nav-link href="#paypal">PayPal</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#installments">Installment Payments</x-doc-nav-link>
        <x-doc-nav-link href="#options">Options</x-doc-nav-link>
        <x-doc-nav-link href="#promo-codes">Promo Codes</x-doc-nav-link>
        <x-doc-nav-link href="#add-ons">Add-ons</x-doc-nav-link>
        <x-doc-nav-link href="#allocated-seating">Allocated Seating</x-doc-nav-link>
        <x-doc-nav-group label="Managing Sales" href="#managing-sales">
            <x-doc-nav-link href="#sales-list">The Sales List</x-doc-nav-link>
            <x-doc-nav-link href="#refunds">Refunds</x-doc-nav-link>
            <x-doc-nav-link href="#sale-notifications">Sale Notifications</x-doc-nav-link>
            <x-doc-nav-link href="#export">Exporting Sales Data</x-doc-nav-link>
            <x-doc-nav-link href="#importing-attendees">Importing Attendees</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Check-in at the Door" href="#check-in">
            <x-doc-nav-link href="#checkin-dashboard">Check-in Dashboard</x-doc-nav-link>
            <x-doc-nav-link href="#wallet-passes">Wallet Passes</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#waitlist">Waitlist</x-doc-nav-link>
        <x-doc-nav-link href="#interest-list">Interest List</x-doc-nav-link>
        <x-doc-nav-link href="#feedback">Post-Event Feedback</x-doc-nav-link>
        <x-doc-nav-link href="#financial">Financial Information</x-doc-nav-link>
        <x-doc-nav-link href="#embed-widget">Embed Widget</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- General -->
    <section id="general" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>
            General
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Sell tickets directly from your event pages with secure payment processing, automatic confirmation emails, and a QR code on every ticket. <strong class="text-gray-900 dark:text-white">Free registration is unlimited on every plan, charging for a ticket is a Pro feature, and Event Schedule takes no cut of a sale on any plan.</strong></p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Ticketing is set up and run in three places:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">The Tickets tab of an event.</strong> Open an event for editing and choose <strong class="text-gray-900 dark:text-white">Tickets</strong>. It holds how people sign up, the ticket types, and the <strong class="text-gray-900 dark:text-white">Payment</strong>, <strong class="text-gray-900 dark:text-white">Options</strong>, <strong class="text-gray-900 dark:text-white">Promo Codes</strong> and <strong class="text-gray-900 dark:text-white">Add-ons</strong> rows.</li>
            <li><strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales.</strong> Every order and registration, with refunds, the door scanner, the check-in dashboard, and tabs for the waitlist, feedback, passes, installments and gift cards. See <a href="#managing-sales" class="doc-link">Managing Sales</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods.</strong> Where you connect Stripe, PayPal and the other payment methods, once for your account. See <a href="#payment" class="doc-link">Payment</a>.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">No plan takes a platform fee. On eventschedule.com the checkout charge is created on <em>your own</em> connected Stripe account with no application fee attached, and a selfhosted install charges through its own Stripe keys. On Free, Pro and Enterprise alike you pay only your payment processor's own fees.</p>

        <h3 id="tickets-tab" class="doc-subheading">The Tickets tab</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The tab opens on three tiles. Press the one that fits the event, and what it needs appears under it:</p>

        <x-doc-screenshot id="tickets--tickets-tab" alt="The Tickets tab of a saved event: the three tiles with Sell tickets chosen, Not needed under them, a notice to connect Stripe, and the Seating plan field above the ticket types" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tile</th>
                        <th>What it does</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#registration" class="doc-link">Free registration</a></td>
                        <td>Guests sign up with a name and an email, with no payment. An optional limit caps the sign-ups per date.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td><a href="#ticketing" class="doc-link">Sell tickets</a></td>
                        <td>Ticket types with prices, quantities and a checkout. A ticket type priced at zero is unlimited on every plan; charging for one needs Pro.</td>
                        <td>Free, Pro to charge</td>
                    </tr>
                    <tr>
                        <td><a href="#external" class="doc-link">Tickets elsewhere</a></td>
                        <td>Links the event page to someone else's ticketing page. Event Schedule handles no money.</td>
                        <td>Free</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Not needed</strong>, under the tiles, switches ticketing off. It carries a tick while none of the three is on, and with nothing chosen the event has no ticketing at all. Pressing a tile that is already on does nothing: <strong class="text-gray-900 dark:text-white">Not needed</strong> is the only way to switch off.</p>

        <ul class="doc-list mb-6">
            <li>If anyone has already bought or registered, leaving <strong class="text-gray-900 dark:text-white">Sell tickets</strong> or <strong class="text-gray-900 dark:text-white">Free registration</strong> first asks <em>People have already signed up for this event. Change how sign-up works?</em></li>
            <li>The link, price and coupon typed under <strong class="text-gray-900 dark:text-white">Tickets elsewhere</strong> stay with the event when you press another tile, and pressing the tile again shows them. Saving on <strong class="text-gray-900 dark:text-white">Not needed</strong> clears them.</li>
            <li>Once the event has sold anything, the tab's title row shows the count (for example <em>Sold: 42/120</em>) and a <strong class="text-gray-900 dark:text-white">Sales</strong> link that opens the Sales page filtered to this event's name.</li>
        </ul>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Saving with Sell tickets switched off removes the ticket types</div>
            <p>If an event was saved with ticket types and you save it again on <strong class="text-gray-900 dark:text-white">Free registration</strong>, <strong class="text-gray-900 dark:text-white">Tickets elsewhere</strong> or <strong class="text-gray-900 dark:text-white">Not needed</strong>, its ticket types and its add-ons are removed. The save bar says so before you press Save: <em>Saving removes this event's ticket types.</em> Orders already taken stay on the Sales page, and promo codes are kept.</p>
        </div>

        <h3 class="doc-subheading">Which plan you need</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The line runs between giving a ticket away and charging for one. <strong class="text-gray-900 dark:text-white">A ticket type with a price needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or Enterprise</strong>, and a <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so paid selling is open there from the day you install it.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Everything that does not charge money is on every plan, with no monthly count to watch:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Free registration and RSVP</strong> - unlimited, with an optional capacity limit per date</li>
            <li><strong class="text-gray-900 dark:text-white">Zero-price ticket types</strong> - a $0 tier sells without limit, and goes on selling on a Free schedule even where the event also carries paid tiers</li>
            <li><strong class="text-gray-900 dark:text-white">QR codes and scanning at the door</strong> - on every ticket, on every plan, with the live <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a> the Pro part</li>
            <li><strong class="text-gray-900 dark:text-white"><a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointment bookings</a></strong> - one free type, with its own allowance; charging for one needs Pro, like a priced ticket</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">On a Free schedule the paid rows of an event do not go on sale, whichever payment method they are set to: free registration and free ticket tiers keep working, and an event with nothing left to sell falls back to an <strong class="text-gray-900 dark:text-white">Add to Calendar</strong> button rather than a dead buy button. The Tickets tab says so as soon as a price is typed, in a banner titled <em>Selling paid tickets is a Pro feature</em>. Subscribing opens the paid rows immediately, with no re-publishing and no change to the event.</p>

        <h3 id="selling-trial" class="doc-subheading">Try selling free for 7 days</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">On eventschedule.com, that banner offers a <strong class="text-gray-900 dark:text-white">7-day selling trial</strong>: press <strong class="text-gray-900 dark:text-white">Sell tickets free for 7 days</strong>, there or on the schedule's <a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">Plan tab</a>. It needs no card and changes nothing about your plan: for seven days the schedule's priced tickets go on sale as if it were on Pro, so you can take your first real sales before deciding. It covers paid tickets only. Passes, installments, add-ons and the other Pro features stay on Pro.</p>
        <ul class="doc-list mb-6">
            <li>Only the schedule owner can start it, and each account gets one, on one schedule</li>
            <li>The Tickets tab and the Plan tab show the days left, and you get an email 3 days before it ends and on the last day</li>
            <li>When it ends, priced tickets stop selling until you subscribe. Everyone who bought keeps their ticket, check-in keeps working, and you can still refund any sale</li>
        </ul>

        <h3 id="plan-table" class="doc-subheading">What each plan includes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so nothing in the right-hand column is held back there.</p>

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
                        <td><a href="#registration" class="doc-link">Free registration</a>, with a limit per date and a <a href="#waitlist" class="doc-link">waitlist</a> on a full date</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td><a href="#free-events" class="doc-link">Ticket types priced at zero</a></td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Ticket types with a price</td>
                        <td>Not included, except during the <a href="#selling-trial" class="doc-link">selling trial</a></td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Platform fee on a sale</td>
                        <td>None</td>
                        <td>None</td>
                    </tr>
                    <tr>
                        <td>Any number of <a href="#ticket-types" class="doc-link">ticket types</a>, with quantities, sales windows, volume discounts, a cap per order and questions asked per ticket type</td>
                        <td>Yes</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>A QR code on every ticket and <a href="#check-in" class="doc-link">scanning at the door</a></td>
                        <td>Yes</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#refunds" class="doc-link">Refunds</a> from the Sales page, full or partial</td>
                        <td>Only for sales taken while on Pro or on the selling trial</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>The <a href="#interest-list" class="doc-link">interest list</a>, the <a href="#cart" class="doc-link">multi-event cart</a> and the <a href="#embed-widget" class="doc-link">RSVP embed</a></td>
                        <td>Yes</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#sale-notifications" class="doc-link">Sale notification emails</a></td>
                        <td>The first paid sale of each event</td>
                        <td>Every sale</td>
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
                        <td><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Passes</a>, <a href="#installments" class="doc-link">installments</a>, individual tickets, <a href="#checkout-fields" class="doc-link">custom checkout fields</a> asked once per order</td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>Ticket <a href="#waitlist" class="doc-link">waitlist</a>, <a href="#export" class="doc-link">CSV export</a>, <a href="#importing-attendees" class="doc-link">bulk import</a>, <a href="#embed-widget" class="doc-link">ticket embed</a></td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#feedback" class="doc-link">Post-event feedback</a></td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Registration -->
    <section id="registration" class="doc-section">
        <h3 class="doc-subheading">Free registration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The first tile. Guests sign up with a name and an email, with no payment and nothing to connect. It suits meetups, community events and open gatherings where you want to know who is coming.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event and open the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab</li>
            <li>Press <strong class="text-gray-900 dark:text-white">Free registration</strong></li>
            <li>Optionally set a <strong class="text-gray-900 dark:text-white">Registration Limit</strong>, the most sign-ups each date takes. Leave it blank for unlimited</li>
            <li>Optionally open the <strong class="text-gray-900 dark:text-white">More options</strong> row under it to ask for a phone number, write <strong class="text-gray-900 dark:text-white">Registration Notes</strong> or add custom fields. See <a href="#options" class="doc-link">Options</a></li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors then see a <strong class="text-gray-900 dark:text-white">Register</strong> button on your event page. After registering they receive a confirmation email with a QR code for check-in, and the registration appears in your <a href="#managing-sales" class="doc-link">Sales</a> list, where its total reads <em>Registered</em>. Whatever you write in <strong class="text-gray-900 dark:text-white">Registration Notes</strong> (directions, parking, a dress code) goes into that email and onto their ticket.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Registrants can cancel themselves from the ticket page linked in their email, which frees the spot again.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">The Registration Limit is per date.</strong> On a recurring event each occurrence keeps its own count, so a limit of 30 means 30 people per date, not 30 across the series.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Registration never asks <a href="#plan-table" class="doc-link">which plan you are on</a>: the sign-ups, the limit, the <a href="#waitlist" class="doc-link">waitlist</a> on a full date and the <a href="#embed-widget" class="doc-link">RSVP embed widget</a> are free on every plan. Two things in the <strong class="text-gray-900 dark:text-white">More options</strong> row need Pro: custom fields, and <strong class="text-gray-900 dark:text-white">Individual tickets</strong>, which the form locks below Pro.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">If you have <x-link href="{{ route('marketing.docs.developer.webhooks') }}">webhooks</x-link> configured, registrations fire <code class="doc-inline-code">sale.created</code> and cancellations fire <code class="doc-inline-code">sale.cancelled</code>.</p>
    </section>

    <!-- Ticketing -->
    <section id="ticketing" class="doc-section">
        <h3 class="doc-subheading">Sell tickets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The middle tile, for paid events and for free ones that need more than one ticket type. The ticket types appear under the tiles, and four rows under them hold everything else. Creating ticket types and giving tickets away work on the Free plan; <a href="#plan-table" class="doc-link">charging for one</a> needs Pro.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Setting up ticket sales</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Connect a payment method first, under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong>. Without one, buyers can only pay cash at the door. See <a href="#payment" class="doc-link">Payment</a>.</li>
            <li>Edit your event and open the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab</li>
            <li>Press <strong class="text-gray-900 dark:text-white">Sell tickets</strong></li>
            <li>Fill in the first ticket type (its fields are described below), then use <strong class="text-gray-900 dark:text-white">+ Add Type</strong> for each further one</li>
            <li>Open the <a href="#payment-row" class="doc-link">Payment</a> row and choose a payment method and currency</li>
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
                        <td>In the event's currency, which is shown beside the label and set in the Payment row. Leave it blank (or enter <code class="doc-inline-code">0</code>) for a free ticket.</td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td>Leave it blank for unlimited. On a recurring event the count is per date.</td>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <td>The name shown to buyers, such as General Admission or VIP. Optional on a single ticket type, required as soon as there is more than one.</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>Optional. Choose <strong class="text-gray-900 dark:text-white">+ Add Description</strong> under the ticket type; it supports Markdown.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A <strong class="text-gray-900 dark:text-white">Buy Tickets</strong> button then appears on your event page, or <strong class="text-gray-900 dark:text-white">Get Tickets</strong> when every type is free. Both labels can be reworded under <strong class="text-gray-900 dark:text-white">Customize &rarr; Custom Labels</strong> <x-doc-badge plan="pro" /> on the schedule's edit page.</p>

        <h4 id="ticket-rows" class="text-base font-semibold text-gray-900 dark:text-white mb-4">The four rows</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Under the ticket types, each row says what it holds on one line, or <em>None</em> while it holds nothing. Press a row to open it in place; one is open at a time.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>What it holds</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#payment-row" class="doc-link">Payment</a></td>
                        <td>The payment method, the currency, payment instructions for cash, and <a href="#installments" class="doc-link">monthly installments</a> on Stripe. Its line reads, for example, <em>Stripe &middot; USD</em>.</td>
                        <td>Free; installments are Pro</td>
                    </tr>
                    <tr>
                        <td><a href="#options" class="doc-link">Options</a></td>
                        <td>Checkout switches, custom fields, ticket notes and a terms link.</td>
                        <td>Free; two settings are Pro</td>
                    </tr>
                    <tr>
                        <td><a href="#promo-codes" class="doc-link">Promo Codes</a></td>
                        <td>Discount codes. The line lists the codes.</td>
                        <td>Pro</td>
                    </tr>
                    <tr>
                        <td><a href="#add-ons" class="doc-link">Add-ons</a></td>
                        <td>Optional extras buyers can attach to an order. The line lists their names.</td>
                        <td>Pro</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The Promo Codes and Add-ons rows still open on the Free plan: they show what the feature does and an upgrade panel in place of the editor. On a Venue schedule with <a href="#allocated-seating" class="doc-link">allocated seating</a>, a <strong class="text-gray-900 dark:text-white">Seating plan</strong> selector sits above the ticket types.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Reuse a setup on the next event</div>
            <p>At the bottom of the Tickets tab, turn on <strong class="text-gray-900 dark:text-white">Save as default</strong> before saving. New events on this schedule then start with the same ticket types, payment method, currency, options, promo codes and add-ons.</p>
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

        <p class="text-gray-600 dark:text-gray-300 mb-6">A ticket type sells by the number, not by the seat: the buyer takes three, not seats 12, 13 and 14. To sell the seats themselves, see <a href="#allocated-seating" class="doc-link">Allocated Seating</a>. There is no pay-what-you-wish pricing either: every ticket type has one fixed price, and a blank price means free.</p>

        <h4 id="ticket-settings" class="text-base font-semibold text-gray-900 dark:text-white mb-4">Ticket settings</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A ticket type carries more than its Price, Quantity and Type: a line of links under those fields adds one more setting each. In the form's order:</p>
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
                        <td>Custom Fields (Per Ticket)</td>
                        <td>Questions that belong to one ticket type, such as a meal choice on the dinner ticket. A buyer is asked them once when their order includes that type, or once for each guest when <a href="#options" class="doc-link">Individual tickets</a> and <strong class="text-gray-900 dark:text-white">Collect ticket fields per guest</strong> are on. Added with <strong class="text-gray-900 dark:text-white">+ Add Field</strong>, up to 10 per ticket type.</td>
                    </tr>
                    <tr>
                        <td><a href="#volume-discount" class="doc-link">Volume Discount</a></td>
                        <td>A percentage or fixed amount off once a buyer takes a minimum quantity. Added with <strong class="text-gray-900 dark:text-white">+ Add Discount</strong>.</td>
                    </tr>
                    <tr>
                        <td><a href="#max-per-order" class="doc-link">Max Per Order</a></td>
                        <td>A cap on how many of this type one order may hold. Added with <strong class="text-gray-900 dark:text-white">+ Add Limit</strong>.</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>Text shown to buyers under the ticket type. Added with <strong class="text-gray-900 dark:text-white">+ Add Description</strong>; supports Markdown.</td>
                    </tr>
                    <tr>
                        <td>Pass or subscription <x-doc-badge plan="pro" /></td>
                        <td>The switch <strong class="text-gray-900 dark:text-white">This is a pass or subscription (multi-use)</strong> turns the type into a multi-use pass. See <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a>.</td>
                    </tr>
                    <tr>
                        <td>Ticket sales start</td>
                        <td>One absolute date and time at which this type goes on sale. Appears once <strong class="text-gray-900 dark:text-white">Set when sales start and end</strong> is on, in the <a href="#options" class="doc-link">Options</a> row.</td>
                    </tr>
                    <tr>
                        <td>Ticket sales end</td>
                        <td>One absolute date and time at which this type stops selling. Appears with the start.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything in this table works on the Free plan except the pass switch, which is locked below Pro. A ticket type is removed with <strong class="text-gray-900 dark:text-white">Remove</strong>, which appears once the event has more than one.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Sales windows are fixed instants, not offsets</div>
            <p>A sales start or end is a single date and time, not "two hours before the event". On a recurring event that one instant governs the whole series, so it is best used for a one-off pre-sale window rather than a per-occurrence cutoff. To stop selling at each occurrence automatically, leave the dates blank: sales close at the start time by default, or at the event's end if <strong class="text-gray-900 dark:text-white">Allow sales after event starts</strong> is on.</p>
        </div>

        <h4 id="combined-total" class="text-base font-semibold text-gray-900 dark:text-white mb-4">Individual quantities or one combined pool</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When an event has two or more ticket types and they all carry the <em>same</em> quantity, a choice appears under the list:</p>
        <ul class="doc-list mb-4">
            <li><strong class="text-gray-900 dark:text-white">Individual Quantities</strong> - each ticket type is counted separately, so the capacity is the sum. This is the default.</li>
            <li><strong class="text-gray-900 dark:text-white">Combined Total</strong> - all types draw on a single pool of that size. Use it when 100 means 100 people through the door however they split between General and VIP.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The choice is hidden when the quantities differ, when any quantity is blank, when there is only one seat-selling type, or when the event uses a seating plan. Passes are ignored in that judgement, since a pass does not define seat capacity.</p>

        <h4 id="volume-discount" class="text-base font-semibold text-gray-900 dark:text-white mb-4">Volume discount</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose <strong class="text-gray-900 dark:text-white">+ Add Discount</strong> on a ticket type to reward buying in bulk. Set the <strong class="text-gray-900 dark:text-white">Minimum quantity</strong> that unlocks it (two or more), then the <strong class="text-gray-900 dark:text-white">Type</strong> (<strong class="text-gray-900 dark:text-white">Percentage</strong> or <strong class="text-gray-900 dark:text-white">Fixed amount</strong>) and the <strong class="text-gray-900 dark:text-white">Discount value</strong>. A group of four booking together gets the discount; a single buyer does not.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The discount comes off the line for that ticket type, not the whole order, and never off <a href="#add-ons" class="doc-link">add-ons</a>: four discounted tickets plus a parking add-on means the four tickets are discounted and the parking is not. A <a href="#promo-codes" class="doc-link">promo code</a> stacks on top. The volume discount is taken off first and the code is then worked out on what is left, so the two never double-count the same money.</p>

        <h4 id="max-per-order" class="text-base font-semibold text-gray-900 dark:text-white mb-4">Max per order</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Choose <strong class="text-gray-900 dark:text-white">+ Add Limit</strong> to cap how many of one ticket type a single buyer can take in one order. This is what keeps a two-for-one early bird from being bought out by the first person through the door, and it is separate from the ticket type's total <strong class="text-gray-900 dark:text-white">Quantity</strong>: the quantity is how many exist, the limit is how many one order may hold. Add-ons take the same limit.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Passes &amp; subscriptions <x-doc-badge plan="pro" /></h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">This is a pass or subscription (multi-use)</strong> to make a ticket type one purchase a guest reuses across many events. Four types are offered: a visit pass with a fixed number of visits, an unlimited membership, a festival pass good once per event, and - on a recurring event only - a season pass.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Advance booking is <strong class="text-gray-900 dark:text-white">off by default</strong>, which makes a pass scan-at-the-door only. Turn on <strong class="text-gray-900 dark:text-white">Let holders book seats in advance</strong> first, and the per-date seat cap and the cancellation-deadline settings appear with it. Passes also keep one shared inventory bucket rather than a count per date. See <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> for the full guide.</p>
    </section>

    <!-- Free Tickets -->
    <section id="free-events" class="doc-section">
        <h3 class="doc-subheading">Free Tickets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A free event that needs more than one ticket type (General and VIP, say), a quantity per type or a cap per order uses <a href="#ticketing" class="doc-link">Sell tickets</a> with the price left blank:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Press <strong class="text-gray-900 dark:text-white">Sell tickets</strong> on the Tickets tab</li>
            <li>Create a ticket type</li>
            <li>Leave the <strong class="text-gray-900 dark:text-white">Price</strong> blank, or enter <code class="doc-inline-code">0</code></li>
            <li>Set a <strong class="text-gray-900 dark:text-white">Quantity</strong> if you have capacity constraints</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Visitors take a free ticket through the same checkout, with nothing to pay, and the button on the event page reads <strong class="text-gray-900 dark:text-white">Get Tickets</strong>. They receive a confirmation email with a QR code, and you have a list of who is coming. Add <strong class="text-gray-900 dark:text-white">Ticket Notes</strong> (in the <a href="#options" class="doc-link">Options</a> row) to include directions or other instructions in that email. A holder of a free ticket can cancel it themselves from their ticket page, which releases the spot; paid orders are cancelled by you from the <a href="#managing-sales" class="doc-link">Sales</a> page.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A ticket type priced at zero sells on <a href="#plan-table" class="doc-link">every plan</a>, Free included. On a Free schedule whose event mixes a free tier with paid ones, the free tier stays on sale and only the paid rows sit out until you subscribe.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The three tiles are one choice, so an event cannot have <a href="#registration" class="doc-link">Free registration</a> and ticket types at once. For a plain headcount, Free registration is the shorter route, and its per-date limit and waitlist are free on every plan. For free and paid options side by side, use Sell tickets with a zero-price type beside the paid ones.</p>
    </section>

    <!-- External -->
    <section id="external" class="doc-section">
        <h3 class="doc-subheading">Tickets elsewhere</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The third tile. Use it when tickets are sold somewhere else (Eventbrite, Ticketmaster, a box office of your own). Event Schedule handles no money here: the event page links out. It is available on every plan.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Registration URL</td>
                        <td>The external ticketing page. It becomes a <strong class="text-gray-900 dark:text-white">View Event</strong> button on your event page, opening in a new tab.</td>
                    </tr>
                    <tr>
                        <td>Price</td>
                        <td>A display-only price, with its currency beside it. Leave it blank if you do not know it; enter <code class="doc-inline-code">0</code> and the event page reads "Free entry". It only shows once a Registration URL is set.</td>
                    </tr>
                    <tr>
                        <td>Coupon Code</td>
                        <td>Shown under the price so attendees can use it on the external platform. Event Schedule never validates it.</td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td>What the coupon is worth, as a percentage or an amount in the event's currency. Shown beside the code, so the event page can read <code class="doc-inline-code">Coupon Code: SAVE20 &bull; 15% off</code> rather than sending guests to the external site to find out. Leave it blank if the coupon has no fixed value.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Multi-event cart -->
    <section id="cart" class="doc-section">
        <h3 class="doc-subheading">Buying Several Events at Once</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A visitor browsing your schedule can collect tickets to more than one event and pay for the lot in a single checkout. On any event with tickets they choose their quantities and select <strong class="text-gray-900 dark:text-white">Add to cart</strong> instead of Checkout, then carry on browsing. A cart button appears in the corner with a running count; opening it lists everything gathered so far with a running total, and one Checkout pays for all of it. A gift card can be applied to the whole order.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Afterwards the buyer lands on a page listing every event they bought, each linking to its own ticket. There is no combined ticket: every event is scanned with its own code, because each door only knows about its own event. The confirmation emails arrive one per event for the same reason, and where <a href="#wallet-passes" class="doc-link">wallet passes</a> are enabled each live event on that page gets its own Add to Google Wallet button.</p>

        <h4 id="cart-rules" class="text-base font-semibold text-gray-900 dark:text-white mb-4">What can share a cart</h4>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Events that agree on three things:</strong> the same owner, the same ticket currency and the same payment method. A single payment cannot be split across payment accounts, currencies or payment rails, and the cart says so when an event cannot join.</li>
            <li><strong class="text-gray-900 dark:text-white">Stripe, PayPal and cash.</strong> Invoice Ninja, Payfast and a payment link are not supported, since each sends the buyer to a page built for one event.</li>
            <li><strong class="text-gray-900 dark:text-white">Not events using individual tickets.</strong> They keep their own checkout: the cart collects one name and email for the whole purchase and has nowhere to put a guest list.</li>
            <li><strong class="text-gray-900 dark:text-white">Two dates of one recurring event</strong> count as two entries, so a visitor can take Friday and Saturday in one order.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Nothing about the cart is trusted at checkout. Prices shown in the panel are for orientation: every ticket is re-read and re-priced from your event when the buyer checks out. If any part of the order can no longer be filled, the whole order is refused rather than charged in part, and the cart names the event so the buyer can remove it and continue.</p>
    </section>

    <!-- Payment -->
    <section id="payment" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Payment
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Before you can take money online you need to connect a payment method. Payment methods belong to your account, not to a single event: you connect them once under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong>, where each has a row that says whether it is connected, and you pick one per event in the event's <strong class="text-gray-900 dark:text-white">Payment</strong> row. Six methods are offered, listed here in the order the event form shows them:</p>

        <div class="doc-table-wrap" id="payment-setup">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>How the buyer pays</th>
                        <th>Refund on the Sales page</th>
                        <th>Multi-event cart</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cash</td>
                        <td>At the door. The order is created unpaid and you mark it paid. Always available, with nothing connected.</td>
                        <td>Mark as Refunded</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#stripe" class="doc-link">Stripe</a></td>
                        <td>By card. The money goes straight to your own Stripe account. The only method that offers <a href="#installments" class="doc-link">monthly installments</a>.</td>
                        <td>Refund Ticket, in full or in part</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td><a href="#invoiceninja-modes" class="doc-link">Invoice Ninja</a></td>
                        <td>Through an invoice or a payment link in your own Invoice Ninja company.</td>
                        <td>Mark as Refunded</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>Payment Link</td>
                        <td>On a link you already use, such as a Venmo, Cash App or bank transfer page. Event Schedule never hears from that provider, so any refund happens there too.</td>
                        <td>Mark as Refunded</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td><a href="#payfast" class="doc-link">Payfast</a></td>
                        <td>By card, Instant EFT, Capitec Pay and the other South African methods. Offered on events priced in rand (ZAR) only.</td>
                        <td>Mark as Refunded</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td><a href="#paypal" class="doc-link">PayPal</a></td>
                        <td>From a PayPal balance or by card. The ticket is issued as soon as the buyer returns. Offered on events priced in a currency PayPal settles.</td>
                        <td>Refund Ticket, in full or in part</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Connecting each method is also covered, row by row, in <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Account Settings</a>.</p>

        <h3 id="payment-row" class="doc-subheading">The Payment row of an event</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">With <strong class="text-gray-900 dark:text-white">Sell tickets</strong> on, the <strong class="text-gray-900 dark:text-white">Payment</strong> row is the first of the four under the ticket types. It holds:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Payment Method</td>
                        <td>Cash, plus every method you have connected that can settle the event's currency. <strong class="text-gray-900 dark:text-white">Manage payment methods</strong> under it opens Settings in a new tab. A method the event was saved with that can no longer be used stays in the list marked <em>no longer available</em> until you pick another.</td>
                    </tr>
                    <tr>
                        <td>Currency</td>
                        <td>The currency of every price on the event. It locks once the event has taken money, with a note saying why. See <a href="#currency-lock" class="doc-link">why it locks</a>.</td>
                    </tr>
                    <tr>
                        <td>Payment Instructions</td>
                        <td>Shown for Cash only. Guests see the text when they check out.</td>
                    </tr>
                    <tr>
                        <td>Let buyers pay in monthly installments <x-doc-badge plan="pro" /></td>
                        <td>Shown for Stripe only. See <a href="#installments" class="doc-link">Installment Payments</a>.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The row warns you when a priced ticket has nowhere to send its money, and the same warning appears under the three tiles with a <strong class="text-gray-900 dark:text-white">Payment</strong> link that opens the row:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Connect Stripe to get paid</strong> when no payment method is connected. Opening the row shows a panel with a <strong class="text-gray-900 dark:text-white">Connect Stripe</strong> button, which opens Settings in a new tab; reload the event page once you are connected.</li>
            <li><strong class="text-gray-900 dark:text-white">No payment method for EUR</strong> (with your event's currency) when you have a method connected but none that can settle that currency. Change the event's currency or connect one that can.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">An event still saves and publishes in either state, and buyers can pay cash at the door, so connect a method before you announce a paid event.</p>

        <h3 id="stripe" class="doc-subheading">Connecting Stripe</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong> and open the <strong class="text-gray-900 dark:text-white">Stripe</strong> row</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect Stripe</strong></li>
            <li>Complete the Stripe onboarding process</li>
            <li>Once connected, Stripe appears as a payment method in the event's <strong class="text-gray-900 dark:text-white">Payment</strong> row</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Stripe verifies a new account asynchronously, so there is a short window after onboarding where the account is linked but not yet ready to charge. The Stripe row in Settings reads <em>Setup not finished</em> and the event's Payment row shows a "verifying" notice during that time. If you finish onboarding in another tab, reload the event page to pick up the change.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A selfhosted install has no Connect button: Stripe is set up once for the whole install by its administrator, and the Stripe row says whether it is configured. See the <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Payments guide</a>.</p>

        <h3 id="invoiceninja-modes" class="doc-subheading">Invoice Ninja Modes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">We recommend using Stripe with Invoice Ninja for the best experience. Invoice Ninja provides additional features like invoicing, payment reminders, and financial reporting.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Once Invoice Ninja is connected, its row under <strong class="text-gray-900 dark:text-white">Settings &rarr; Payment Methods</strong> offers a <strong class="text-gray-900 dark:text-white">Checkout mode</strong> with two choices, <strong class="text-gray-900 dark:text-white">Invoice</strong> and <strong class="text-gray-900 dark:text-white">Payment link</strong>. Connecting it is covered in <a href="{{ route('marketing.docs.account_settings') }}#invoice-ninja" class="doc-link">Account Settings</a>.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ticket selection and promo codes are handled in Event Schedule. An invoice is created in Invoice Ninja for each purchase. Supports multiple promo codes and per-ticket promo targeting. Buyers can optionally create an Event Schedule account during checkout.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment link</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Buyers select tickets and enter promo codes on the Invoice Ninja purchase page. Invoices are grouped in Invoice Ninja, making bulk management easier. Supports one promo code per event (applied to all tickets). Buyers can optionally create an Event Schedule account during checkout. See the <x-link href="https://invoiceninja.github.io/docs/user-guide/subscriptions" target="_blank">Invoice Ninja payment link docs</x-link> for more details.</p>
            </div>
        </div>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Invoice</th>
                        <th>Payment link</th>
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
                        <td>Invoices grouped in Invoice Ninja</td>
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

        <p class="text-gray-600 dark:text-gray-300 mb-6">Start with Invoice mode for maximum flexibility. Switch to Payment link mode if you want invoices grouped together in Invoice Ninja.</p>

        <h3 id="payfast" class="doc-subheading">Connecting Payfast</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><x-link href="https://payfast.io" target="_blank">Payfast</x-link> is a South African gateway, useful where Stripe is not available. It settles in rand (ZAR) only, so Payfast appears as an option only on events priced in ZAR. If an event is later switched to another currency, or its method is set through the API, checkout refuses rather than charging the wrong currency. See <a href="#payfast-refused" class="doc-link">When a Payfast checkout is refused</a>.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In Payfast, open <strong class="text-gray-900 dark:text-white">Settings</strong> and note your <strong class="text-gray-900 dark:text-white">Merchant ID</strong> and <strong class="text-gray-900 dark:text-white">Merchant Key</strong></li>
            <li>Set a <strong class="text-gray-900 dark:text-white">passphrase</strong> in the same Payfast screen if you have not already</li>
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong> and open the <strong class="text-gray-900 dark:text-white">Payfast</strong> row</li>
            <li>Enter all three values and press <strong class="text-gray-900 dark:text-white">Connect</strong></li>
            <li>Payfast now appears in the <strong class="text-gray-900 dark:text-white">Payment</strong> row of any event priced in ZAR</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The passphrase is required rather than optional. It is what lets us verify that a payment notification genuinely came from Payfast, so without one there is no way to tell a real payment from a forged one. Setting it on your Payfast account also makes Payfast reject unsigned checkout requests, which protects your merchant account beyond this integration.</p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Your site may already have an account</div>
            <p>On a selfhosted site, the administrator can configure one Payfast account for everyone. If the Payfast row says <strong class="text-gray-900 dark:text-white">Provided by this installation</strong>, skip the steps above: Payfast is already available on your ZAR events and payments settle into the site's account. Entering your own details there still works and takes precedence, so you are paid into your own account instead; disconnect them to go back. Selfhost administrators: see the <a href="{{ route('marketing.docs.selfhost.stripe') }}#payfast" class="doc-link">Payments guide</a>.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">By default Payfast shows buyers every method your account supports. To send them straight to one instead, tick exactly one entry under <strong class="text-gray-900 dark:text-white">Payment methods</strong>. Ticking several, or none, leaves the choice to Payfast.</p>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Testing with the sandbox</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">Test mode</strong> to send payments to Payfast's sandbox instead of taking real money. Payfast's public sandbox credentials are merchant ID <code class="doc-inline-code">10000100</code> and merchant key <code class="doc-inline-code">46f0cd694581a</code>. You still need a passphrase: set one in your <x-link href="https://sandbox.payfast.co.za" target="_blank">Payfast sandbox account</x-link> and enter it here alongside them, because all three are required whether or not test mode is on.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">While test mode is on, the payment page shows buyers a clear test-mode notice, and the payment method appears with a test-mode label on the event form. Turn test mode off before you sell real tickets.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Payfast cannot reach a notification URL on <code class="doc-inline-code">localhost</code>, so a sandbox purchase only completes end to end on a publicly reachable install. Selfhosted installs need no extra configuration for the notification to be accepted: it is authenticated by its signature and by asking Payfast to confirm it, not by the address it arrives from, so running behind Cloudflare, a reverse proxy or Docker changes nothing.</p>

        <h4 id="payfast-refused" class="font-semibold text-gray-900 dark:text-white mb-2">When a Payfast checkout is refused</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Two orders never reach Payfast, because it would reject them on its own page after the seats were already held: an order under <strong class="text-gray-900 dark:text-white">R5.00</strong>, Payfast's minimum, and any event whose currency is not ZAR. In both cases the buyer is returned to the ticket page with a message, and the seats go straight back on sale. If you see that on your own event, check the event's <strong class="text-gray-900 dark:text-white">Currency</strong> in the Payment row: an event can keep Payfast selected after its currency is changed, and it then shows in the dropdown marked <em>no longer available</em> until you pick something else.</p>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Refunds</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Refunds are issued from your own Payfast dashboard. On a Payfast sale the Sales page offers <strong class="text-gray-900 dark:text-white">Mark as Refunded</strong>, which records the refund without moving money. Stripe and PayPal are the exceptions: those sales are refunded from the Sales page and the money goes back automatically. See <a href="#refunds" class="doc-link">Refunds</a>.</p>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What Payfast does not do</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A Payfast event cannot be combined with others in the <a href="#cart" class="doc-link">multi-event cart</a>, since a Payfast payment covers one event, and it cannot offer <a href="#installments" class="doc-link">monthly installments</a>, which need a card the gateway can charge again later. <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift cards</a> cannot be sold through Payfast either. Everything else works normally: promo codes, add-ons, volume discounts, per-attendee tickets.</p>

        <h3 id="paypal" class="doc-subheading">Connecting PayPal</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><x-link href="https://www.paypal.com" target="_blank">PayPal</x-link> is worth connecting where Stripe is not available, or where your buyers would rather pay from a PayPal balance than type a card into a page they have not seen before. Like Stripe, it confirms the payment and issues the ticket with no manual step.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Sign in at <x-link href="https://developer.paypal.com" target="_blank">developer.paypal.com</x-link> and open <strong class="text-gray-900 dark:text-white">Apps &amp; Credentials</strong></li>
            <li>Stay on the <strong class="text-gray-900 dark:text-white">Live</strong> tab (the Sandbox tab issues a different pair, for testing), and create an app if you have not already</li>
            <li>Copy its <strong class="text-gray-900 dark:text-white">Client ID</strong> and <strong class="text-gray-900 dark:text-white">Secret</strong></li>
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Settings &rarr; Payment Methods</strong> and open the <strong class="text-gray-900 dark:text-white">PayPal</strong> row</li>
            <li>Paste both and press <strong class="text-gray-900 dark:text-white">Connect</strong>. We check them with PayPal before storing them, so a typo is caught here rather than by a buyer</li>
            <li>PayPal now appears in the <strong class="text-gray-900 dark:text-white">Payment</strong> row of any event priced in a currency it settles</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">PayPal settles a fixed list of currencies, and an event priced in anything else will not offer it. Three currencies PayPal does support (the Hungarian forint, the Japanese yen and the New Taiwan dollar) are deliberately left out. PayPal will not accept an amount with decimals in any of them, and a percentage discount here can produce one, so an event priced that way would have money taken and the ticket withheld. Rather than let that happen, PayPal is not offered for those three at all.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Payments that PayPal holds for review are the one case where a ticket is not issued at once. The sale stays unpaid, its seats stay held rather than expiring, and the buyer is told the payment is being reviewed rather than being asked to pay again. That holds on every event of the order, so nobody can accidentally pay twice for the same basket. We also ask PayPal not to accept funding that takes days to settle, such as an eCheck, so this should be a short wait rather than an open-ended one.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Finishing a reviewed payment (issuing the ticket if PayPal clears it, or letting the buyer try again if PayPal declines it) depends on PayPal notifying us, which needs a webhook. We register one for you when you connect your own PayPal account, so there is nothing to do. If your site provides one PayPal account for everyone, that registration does not happen and the administrator has to add a listener themselves; without it a reviewed payment on such a site stays unresolved. Selfhost administrators: see the <a href="{{ route('marketing.docs.selfhost.stripe') }}#paypal" class="doc-link">Payments guide</a>.</p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Your site may already have an account</div>
            <p>On a selfhosted site, the administrator can configure one PayPal account for everyone, exactly as with Payfast. If the PayPal row says <strong class="text-gray-900 dark:text-white">Provided by this installation</strong>, skip the steps above. Entering your own details still takes precedence, so you are paid into your own account instead; disconnect them to go back. Selfhost administrators: see the <a href="{{ route('marketing.docs.selfhost.stripe') }}#paypal" class="doc-link">Payments guide</a>.</p>
        </div>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Testing with the sandbox</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">Test mode</strong> to use PayPal's sandbox instead of taking real money. Unlike a single on/off flag, the sandbox is a separate environment with its own credentials: open <strong class="text-gray-900 dark:text-white">Apps &amp; Credentials</strong> at developer.paypal.com, switch to the <strong class="text-gray-900 dark:text-white">Sandbox</strong> tab, and paste that app's Client ID and Secret. Your live pair will not work in test mode and the sandbox pair will not work outside it, so the tab you copied from has to match the toggle.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You also need somebody to play the buyer. PayPal's sandbox creates a personal test account alongside your business one: sign in with that at the checkout. While test mode is on, the payment method reads <strong class="text-gray-900 dark:text-white">PayPal (Test mode)</strong> on the event form and the event's Payment row shows a warning, because a test ticket otherwise looks exactly like a real one. Turn it off before you sell.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">One thing PayPal makes easier than Payfast: the whole purchase works on a laptop. A PayPal payment is confirmed by a call we make out to PayPal rather than by a notification PayPal has to reach us with, so a sandbox purchase completes end to end on <code class="doc-inline-code">localhost</code> with no tunnel and no public hostname.</p>

        <h4 id="paypal-refused" class="font-semibold text-gray-900 dark:text-white mb-2">When a PayPal checkout is refused</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Some orders never reach PayPal, because it would reject them on its own page after the seats were already held. These are an event priced in a currency PayPal does not settle (or in one of the three left out above) and an order PayPal declines to create. In both cases the buyer is returned to the ticket page with a message and the seats go straight back on sale, so nothing is lost.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you see that on your own event, check the event's <strong class="text-gray-900 dark:text-white">Currency</strong> in the Payment row: an event can keep PayPal selected after its currency is changed, and it then shows in the dropdown marked <em>no longer available</em> until you pick something else. The same happens if you disconnect PayPal while an event still names it. There the buyer is returned to the ticket page, so an event left that way is worth catching before a real buyer finds it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Two more outcomes are worth recognising. A buyer who approves the payment, closes the tab and comes back much later may find the reservation has already expired; nothing is charged in that case. And if PayPal reports a total that does not match the order, the sale is held as an <strong class="text-gray-900 dark:text-white">amount mismatch</strong> for you to look at rather than being completed: the money is with PayPal and the ticket is not issued, so it needs a person.</p>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Refunds</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">PayPal is one of only two methods (Stripe is the other) where <strong class="text-gray-900 dark:text-white">Refund Ticket</strong> on the Sales page sends the money back for you. You can return the whole amount or part of it, and the sale's PayPal reference is shown there as a link into your PayPal activity. Refund from the Sales page rather than from PayPal itself: a refund made in your PayPal account is not reported back, so the sale stays paid and its ticket keeps scanning. See <a href="#refunds" class="doc-link">Refunds</a> for partial refunds and for what happens when a refund cannot be confirmed.</p>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What PayPal does not do</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">PayPal cannot offer monthly installments, which need a card the gateway can charge again later, and gift cards cannot be sold through it. It is also not offered on events priced in Hungarian forints, Japanese yen or New Taiwan dollars, or on appointment bookings. Everything else works normally (promo codes, add-ons, volume discounts, per-attendee tickets), and unlike Payfast a PayPal event <em>can</em> be combined with others in the <a href="#cart" class="doc-link">multi-event cart</a>, because the whole basket is taken as one payment.</p>
    </section>

    <!-- Installments -->
    <section id="installments" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
            </svg>
            Installment Payments <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Let buyers spread the cost of an expensive ticket over monthly payments. Useful for courses, retreats and multi-day events announced well in advance: a buyer pays the first installment at checkout and gets their ticket straight away, and the rest is charged automatically to the same card each month.</p>

        <h3 class="doc-subheading">Setting it up</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open your event, go to the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, open the <strong class="text-gray-900 dark:text-white">Payment</strong> row, and turn on <strong class="text-gray-900 dark:text-white">Let buyers pay in monthly installments</strong>. The option appears only when the event is <a href="#payment" class="doc-link">paid through Stripe</a>, because Stripe is the only payment method that can charge a saved card automatically.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr><th>Setting</th><th>What it does</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Number of payments</td>
                        <td>How many monthly payments the order total is split into: 2, 3, 4, 5, 6, 8, 10 or 12. The first is taken at checkout. Amounts that do not divide evenly put the odd cent on the first payment, so 1,000 over three is 333.34 then 333.33 twice.</td>
                    </tr>
                    <tr>
                        <td>Last payment due before the event</td>
                        <td>How much runway you want to chase a failed payment before the doors open, in days: at least 7, and we recommend at least 14. The editor shows you live whether the schedule you have chosen actually finishes in time, and warns you if it would not.</td>
                    </tr>
                    <tr>
                        <td>Only offer installments on orders over</td>
                        <td>Optional. Keeps the option off small orders, so you can offer it on a full course but not a single tasting. Leave it blank to offer it on every order.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every individual payment has to clear Stripe's minimum charge of roughly $0.50, so splitting a small order too many ways withdraws the option. A <a href="#promo-codes" class="doc-link">promo code</a> or gift card applied at checkout can take an order under that line, or under your own minimum, and the buyer will not be offered monthly payments. Installments are also not offered for a basket spanning several events, or for one containing a <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass</a>.</p>

        <h3 id="installments-buyer" class="doc-subheading">What the buyer sees</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">At checkout the buyer chooses between paying in full and paying monthly. Paying in full is selected by default. If they choose monthly they see every payment date and amount before committing, confirm that they authorise the future charges, and are charged only the first payment. There is no interest and no fee: the total is the same either way.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Their ticket is valid from the first payment. Two days before each following payment we email them a reminder naming the card and the amount. Every one of those emails links to their own payment plan page, where they can pay early, clear the whole balance or change their card. That page is the only place their saved card is shown: the ticket page is what the QR code opens, and door staff scan it.</p>

        <h3 id="installments-tracking" class="doc-subheading">Tracking payments</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Installments</strong> tab on your <a href="#managing-sales" class="doc-link">Sales page</a> opens on the figures for each currency (plans, collected, outstanding, and overdue when any are) and an <strong class="text-gray-900 dark:text-white">Expected by month</strong> forecast. Under them, one row per buyer shows how far through their plan they are, the card on file, what has been collected and what is outstanding, when the next payment is due and the plan's status. Overdue plans sort to the top, and once a plan has a payment reference its row opens to list every payment with its date, amount, status and reference.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You get one daily summary of the payments due in the next couple of days, rather than an email per buyer, and an immediate email whenever a payment fails.</p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your sales figures will run ahead of your bank balance</div>
            <p>Sales totals count the full ticket price at the moment of purchase, because the ticket is issued then. So your Sales and <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> figures include money you have not collected yet while plans are still running. The Installments tab is the one that shows what has genuinely been taken.</p>
        </div>

        <h3 id="installments-missed" class="doc-subheading">When a payment fails</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If a card is declined we retry it three more times over the following nine days, emailing the buyer each time and telling them plainly that their ticket is still valid. If their bank asks them to confirm the payment (common in Europe) we send a different email asking them to approve it, and do not count it as a decline.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If the balance is still unpaid after that, the ticket goes <strong class="text-gray-900 dark:text-white">on hold</strong>: it stops scanning at the door until they pay, and paying makes it valid again immediately. A week before the event everyone with an outstanding balance gets a final notice, and you get a list of them, so nobody is surprised at the door.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Running out of retries is the common route to a hold, but not the only one. A plan also goes on hold if a bank authentication request goes unanswered for a week, or if your own Stripe connection is disconnected so nothing can be collected at all. That second one is worth knowing: the buyer has done nothing wrong and cannot fix it, so you are the one we email, and reconnecting Stripe is what restarts collection. For the ordinary routes, the buyer replacing or re-confirming their card lifts the hold and puts the remaining payments back on schedule.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One state deliberately waits for a person: if a charge is interrupted and we cannot tell whether the money moved, we stop rather than retry, because retrying a payment that may already have succeeded is how a buyer gets charged twice. The same applies to a payment that arrives but does not match anything we can apply it to. Both show on the Installments tab as needing your attention, with the Stripe reference to check against your dashboard, and neither is resolved by the buyer changing their card.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Scanning a ticket that is on hold shows your door staff an amber <strong class="text-gray-900 dark:text-white">Overdue balance</strong> screen with the attendee's name, the amount outstanding and how much of the total has been paid, rather than a red rejection. The scan does not check them in, so taking payment or letting the guest in is your call.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Cancelling or refunding an order, cancelling the event, or deleting the schedule all stop the remaining payments immediately. A payment plan can only be refunded in full: <a href="#refunds" class="doc-link">Refund Ticket</a> returns each payment that was already collected, one at a time, and the Installments tab still lists every payment reference so you can check them against your own Stripe dashboard.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted installs</div>
            <p>Installments are available on every schedule, and payments settle through your own Stripe keys rather than a connected account. They do depend on the scheduler: the first payment is taken at checkout either way, but nothing charges the second and later payments unless <code class="doc-inline-code">schedule:run</code> is running on a cron. See <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">selfhosting</a> for setting that up.</p>
        </div>
    </section>

    <!-- Options -->
    <section id="options" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            Options
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The second row of the Tickets tab holds the checkout settings that are not about money. On a <a href="#registration" class="doc-link">Free registration</a> event it is titled <strong class="text-gray-900 dark:text-white">More options</strong> and holds only the phone number, Individual tickets, the custom fields and the notes. The row reads <em>None</em> until something in it is on, then names what is. Top to bottom:</p>

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
                        <td>Adds a phone field to the checkout form. Two tick boxes appear once it is on: <strong class="text-gray-900 dark:text-white">Required</strong> and <strong class="text-gray-900 dark:text-white">Country code</strong>. The number is stored on the sale, shown in the Sales list and included in the <a href="#export" class="doc-link">CSV export</a>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Individual tickets</td>
                        <td>Each attendee gets their own confirmation email and QR code instead of one per order. A second switch under it, <strong class="text-gray-900 dark:text-white">Collect ticket fields per guest</strong>, then asks each ticket type's custom fields once per attendee.</td>
                        <td>Pro</td>
                    </tr>
                    <tr>
                        <td>Allow sales after event starts</td>
                        <td>Keeps selling until the event ends (start time plus duration) instead of stopping at the start time.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Set when sales start and end</td>
                        <td>Reveals the sales start and end fields on each ticket type, described under <a href="#ticket-settings" class="doc-link">Ticket Types</a>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Show unavailable tickets</td>
                        <td>Displays sold out and expired ticket types to visitors in a disabled state, so they can see what was offered.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Expire unpaid tickets</td>
                        <td>Releases unpaid reservations after the number of hours you enter, returning them to stock. Only appears when at least one ticket type has both a price and a limited quantity, since there is nothing to release otherwise.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td><a href="#checkout-fields" class="doc-link">Custom Fields (Per Order)</a></td>
                        <td>Your own questions, asked once for the whole order. See below.</td>
                        <td>Pro</td>
                    </tr>
                    <tr>
                        <td>Ticket Notes</td>
                        <td>Text included in the confirmation email and printed on the attendee's ticket (directions, parking, dress code, what to bring). Supports <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">template variables</a> such as <code class="doc-inline-code">{event_name}</code> and <code class="doc-inline-code">{venue}</code>. On a Free registration event the same field is labelled <strong class="text-gray-900 dark:text-white">Registration Notes</strong>.</td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td>Terms URL</td>
                        <td>A link to your own terms and conditions, printed on every ticket under <em>Terms &amp; Conditions</em>. Leave it blank and the ticket links the installation's own terms of service instead. Buyers are not asked to accept it at checkout.</td>
                        <td>Free</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The four switches from <strong class="text-gray-900 dark:text-white">Allow sales after event starts</strong> to <strong class="text-gray-900 dark:text-white">Expire unpaid tickets</strong>, and the Terms URL, belong to ticket sales and are not shown on a Free registration event. The form locks <strong class="text-gray-900 dark:text-white">Individual tickets</strong> below Pro, with a <strong class="text-gray-900 dark:text-white">See what Pro adds</strong> link under it.</p>

        <h3 id="checkout-fields" class="doc-subheading">Custom Checkout Fields <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Collect additional information from attendees during checkout: dietary requirements, a T-shirt size, a company name, an emergency contact, how they heard about the event. Fields added here, under <strong class="text-gray-900 dark:text-white">Custom Fields (Per Order)</strong>, are asked once for the whole order, and an event takes up to 10 of them.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event</li>
            <li>On the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, open the <strong class="text-gray-900 dark:text-white">Options</strong> row</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Custom Fields (Per Order)</strong>, choose <strong class="text-gray-900 dark:text-white">+ Add Field</strong></li>
            <li>Give it a <strong class="text-gray-900 dark:text-white">Field Name</strong> and a <strong class="text-gray-900 dark:text-white">Type</strong>: Text, Multi-line Text, Yes/No, Date, Dropdown or Multi-select. The last two take a list of <strong class="text-gray-900 dark:text-white">Options</strong></li>
            <li>Tick <strong class="text-gray-900 dark:text-white">Required</strong> if buyers must answer, and drag the fields into the order you want them asked</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Responses are stored with each sale, shown when you open the sale's row on the <a href="#managing-sales" class="doc-link">Sales</a> page, and included in the <a href="#export" class="doc-link">CSV export</a> as one column per field.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">To ask something of one ticket type only, add the field on the ticket type itself: choose <strong class="text-gray-900 dark:text-white">+ Add Field</strong> under it. Those fields are asked once per order that includes the type, each ticket type takes up to 10 of them, and they work on every plan. To have every attendee answer individually (a meal choice, a name for a badge), turn on <strong class="text-gray-900 dark:text-white">Individual tickets</strong> and <strong class="text-gray-900 dark:text-white">Collect ticket fields per guest</strong> above.</p>
    </section>

    <!-- Promo Codes -->
    <section id="promo-codes" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            Promo Codes <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Offer discounts to attendees with promo codes. Buyers enter a code during checkout to receive a discount on their purchase. On the Free plan the row opens on a description of the feature and an upgrade panel.</p>

        <h3 class="doc-subheading">Adding a Promo Code</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event</li>
            <li>On the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, open the <strong class="text-gray-900 dark:text-white">Promo Codes</strong> row</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add Promo Code</strong></li>
            <li>Fill in the code, the discount type and its value (the fields are below)</li>
            <li>Save the event</li>
        </ol>

        <h3 class="doc-subheading">Promo Code Settings</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Promo Code</td>
                        <td>Required. What the buyer types, such as EARLYBIRD or VIP50. Up to 50 characters, shown in capitals.</td>
                    </tr>
                    <tr>
                        <td>Discount Type</td>
                        <td><strong class="text-gray-900 dark:text-white">Percentage</strong> (20% off) or <strong class="text-gray-900 dark:text-white">Fixed amount</strong> (a flat amount off, in the event's currency).</td>
                    </tr>
                    <tr>
                        <td>Discount Value</td>
                        <td>Required. A percentage is capped at 100, and a fixed amount can never discount more than the eligible subtotal.</td>
                    </tr>
                    <tr>
                        <td>Max Uses</td>
                        <td>How many times the code can be used. Leave it blank for unlimited. Once a code has been used, <em>Times Used</em> shows the count beside the Active switch.</td>
                    </tr>
                    <tr>
                        <td>Expires At</td>
                        <td>A date and time when the code stops working.</td>
                    </tr>
                    <tr>
                        <td>Active</td>
                        <td>A switch that turns the code off without deleting it.</td>
                    </tr>
                    <tr>
                        <td>Applies To</td>
                        <td>Shown when the event has more than one ticket type: <strong class="text-gray-900 dark:text-white">All Tickets</strong>, or <strong class="text-gray-900 dark:text-white">Specific Tickets</strong> to tick the types it covers.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Under each promo code, <em>Link that applies this code</em> shows a shareable URL that pre-fills the code at checkout, with a copy button beside it. <strong class="text-gray-900 dark:text-white">Remove</strong> deletes the code when the event is saved.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A promo code never discounts <a href="#add-ons" class="doc-link">add-ons</a>, and it is worked out after any <a href="#volume-discount" class="doc-link">volume discount</a> on the same line, so the two never double-count the same money.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">With Invoice Ninja in <a href="#invoiceninja-modes" class="doc-link">Payment link mode</a>, an event takes one promo code and it applies to every ticket type: the <strong class="text-gray-900 dark:text-white">Applies To</strong> choice and <strong class="text-gray-900 dark:text-white">+ Add Promo Code</strong> are hidden once it has one. Use Invoice mode for several codes with per-ticket targeting.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Gift cards are separate</div>
            <p>Checkout also accepts a gift card code, which spends a prepaid balance instead of applying a discount. A buyer can use a promo code and a gift card on the same order. See <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift Cards</a>.</p>
        </div>
    </section>

    <!-- Add-ons -->
    <section id="add-ons" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.491 48.491 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
            </svg>
            Add-ons <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add-ons are optional purchasable items that customers can include with their ticket order, such as parking passes, merchandise, or meal packages. On the Free plan the row opens on a description of the feature and an upgrade panel.</p>

        <h3 class="doc-subheading">Creating an Add-on</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit your event</li>
            <li>On the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, open the <strong class="text-gray-900 dark:text-white">Add-ons</strong> row</li>
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
            <li><strong class="text-gray-900 dark:text-white">+ Add Limit:</strong> Caps how many of this add-on one order may hold, the same as <a href="#max-per-order" class="doc-link">Max Per Order</a> on a ticket type</li>
        </ul>

        <h3 class="doc-subheading">How Add-ons Work</h3>
        <ul class="doc-list mb-6">
            <li>Add-ons appear in the checkout form only after the customer selects at least one ticket</li>
            <li>Customers choose a quantity for each add-on (or leave it at 0 to skip)</li>
            <li>Add-on totals are added to the ticket total at checkout</li>
            <li>Promo codes and volume discounts do not apply to add-ons</li>
            <li>Add-ons are tracked separately in sales records, the CSV export and confirmation emails</li>
            <li>Add-ons are a Pro feature, as are the <a href="#plan-table" class="doc-link">priced tickets</a> they ride alongside</li>
            <li>Saving the event with <strong class="text-gray-900 dark:text-white">Sell tickets</strong> switched off removes its add-ons, and the save bar says so first: <em>Saving removes this event's add-ons.</em></li>
        </ul>
    </section>

    {{-- Allocated Seating has a page of its own; the anchor stays alive for existing links. --}}
    <section id="allocated-seating" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Allocated Seating <x-doc-badge plan="enterprise" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything above sells by the number. If your venue has rows, you can sell the seats themselves instead: draw the room once as a seating plan, attach it to an event, and buyers pick where they sit. Your box office gets the same map to hold seats back, take a booking over the phone, move somebody or release a single seat.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">It has a guide of its own: <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">Allocated Seating</a>.</p>
    </section>

    <!-- Managing Sales -->
    <section id="managing-sales" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
            Managing Sales
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong>. The page lists the orders and registrations for every event you manage, across all your schedules, newest first and 50 to a page. (The sidebar's <strong class="text-gray-900 dark:text-white">Tickets</strong> entry is a different page: the tickets you hold yourself as an attendee.)</p>

        <x-doc-screenshot id="tickets--sales" alt="The Sales page: a filter box and the Scan Ticket button above a list of orders with their customer, event, total, transaction reference and status" />

        <h3 id="sales-page" class="doc-subheading">The page at a glance</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The title row ends with the page's actions, the forward one last:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Import</strong> <x-doc-badge plan="pro" /> opens <a href="#importing-attendees" class="doc-link">Import Attendees</a></li>
            <li><strong class="text-gray-900 dark:text-white">Check-in</strong> <x-doc-badge plan="pro" /> opens the <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a></li>
            <li><strong class="text-gray-900 dark:text-white">Scan Ticket</strong> opens the <a href="#check-in" class="doc-link">door scanner</a>, on every plan</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Under it sit the tabs. From a tablet up they are a strip, on a phone a dropdown, and a tab with something in it carries a count. A tab only appears when it has something to show:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tab</th>
                        <th>What it lists</th>
                        <th>Shown</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sales</td>
                        <td>Orders and registrations. Described below.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#waitlist" class="doc-link">Waitlist</a></td>
                        <td>People waiting for a place at a sold-out date.</td>
                        <td>While anyone is waiting or has been notified</td>
                    </tr>
                    <tr>
                        <td><a href="#feedback" class="doc-link">Feedback</a></td>
                        <td>Feedback requests still to send, sent and answered.</td>
                        <td>On Pro</td>
                    </tr>
                    <tr>
                        <td><a href="{{ route('marketing.docs.subscriptions') }}#monitoring" class="doc-link">Subscriptions</a></td>
                        <td>Passes sold and the visits made on them.</td>
                        <td>On Pro, or once a pass has been sold</td>
                    </tr>
                    <tr>
                        <td><a href="#installments-tracking" class="doc-link">Installments</a></td>
                        <td>Payment plans, what has been collected and what is due.</td>
                        <td>On Pro, or once a plan exists</td>
                    </tr>
                    <tr>
                        <td><a href="{{ route('marketing.docs.gift_cards') }}#managing" class="doc-link">Gift Cards</a></td>
                        <td>Gift cards sold, their balances and where they were spent.</td>
                        <td>On Pro, or once a card has been sold</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">"On Pro" here means at least one schedule you manage is on Pro or above. The same goes for Import, Check-in and Export, which are hidden rather than shown locked on a Free account.</p>

        <h3 id="sales-list" class="doc-subheading">The Sales list</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The list is a table from a tablet up and a stack of rows on a phone. Click a column heading to sort by it, and again to reverse the order.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>What it shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Customer</td>
                        <td>The buyer's name, with their email and phone under it. An order with individual tickets adds a chip counting its guests.</td>
                    </tr>
                    <tr>
                        <td>Event</td>
                        <td>The event's name, linked to its public page for the date that was bought.</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>The amount and currency, or <em>Registered</em> for a free registration. A promo code and the discount it took, and a gift card and what it paid, show as chips under the amount.</td>
                    </tr>
                    <tr>
                        <td>Transaction Reference</td>
                        <td>The payment's reference, linked to your provider's dashboard where there is a page to link to. A sale you marked paid reads <em>Manual Payment</em>, an imported attendee <em>Manual import</em> and a box office sale <em>Box office</em>.</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>Paid, Unpaid, Cancelled, Refunded or Expired, or <em>amount mismatch</em> for a payment held for review. Beside it: the star rating if the buyer left <a href="#feedback" class="doc-link">feedback</a>, <strong class="text-gray-900 dark:text-white">Refunded so far</strong> once any money has gone back, and a warning while a refund is waiting to be confirmed by the provider.</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>The day the order was placed.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">An arrow before the customer's name opens the row when there is more to see: the buyer's answers to your <a href="#checkout-fields" class="doc-link">custom fields</a>, and on an order with individual tickets each guest, with their own <strong class="text-gray-900 dark:text-white">View Ticket</strong> and <strong class="text-gray-900 dark:text-white">Send Email</strong> links. The ticket types, add-ons, event date and check-in time of each order are in the <a href="#export" class="doc-link">CSV export</a>.</p>

        <h3 id="filtering-sales" class="doc-subheading">Filtering Sales</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Type in the <strong class="text-gray-900 dark:text-white">Filter</strong> box above the list to search by buyer or guest name, email, phone, event name, status or transaction reference. The list updates as you type.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Past events are hidden by default.</strong> Turn on <strong class="text-gray-900 dark:text-white">Include past events</strong>, beside the filter, to bring older sales back into the list. <strong class="text-gray-900 dark:text-white">Export</strong>, at the end of the same row, downloads exactly what the filter and the switch are showing.</p>

        <h3 id="sale-actions" class="doc-subheading">Actions</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open the three-dot menu at the end of a sale's row. It offers only what applies to that sale:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>What it does</th>
                        <th>Offered on</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>View Ticket</td>
                        <td>Opens the attendee's ticket page, with its QR code.</td>
                        <td>Every sale</td>
                    </tr>
                    <tr>
                        <td>Send Email</td>
                        <td>Sends the confirmation email again.</td>
                        <td>Every sale</td>
                    </tr>
                    <tr>
                        <td>Mark Paid</td>
                        <td>For cash or other payments taken outside the app. The buyer is then sent their confirmation email.</td>
                        <td>Unpaid sales</td>
                    </tr>
                    <tr>
                        <td>Refund Ticket</td>
                        <td>Sends money back on a Stripe or PayPal sale, all of it or part. See <a href="#refunds" class="doc-link">Refunds</a>. On an appointment booking it reads <strong class="text-gray-900 dark:text-white">Refund</strong>.</td>
                        <td>Paid Stripe and PayPal sales</td>
                    </tr>
                    <tr>
                        <td>Mark as Refunded</td>
                        <td>Records a refund you make yourself, and moves no money.</td>
                        <td>Paid sales on every other method, except free registrations</td>
                    </tr>
                    <tr>
                        <td>Cancel Ticket</td>
                        <td>Cancels the sale without recording a refund.</td>
                        <td>Paid and unpaid sales</td>
                    </tr>
                    <tr>
                        <td>Delete</td>
                        <td>Removes the sale from the list. A live sale is cancelled first, so its tickets go back on sale.</td>
                        <td>Every sale</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A full refund, a cancellation or a deletion returns the sale's tickets and any allocated seats to stock, gives back any promo code use, credits any gift card balance the buyer spent, stops any remaining installment payments, and notifies the next person on the <a href="#waitlist" class="doc-link">waitlist</a>. A partial refund does none of that: the sale stays paid and every ticket on it stays valid. On an order with individual tickets these actions belong to the buyer's row, not to a guest's.</p>

        <h3 id="refunds" class="doc-subheading">Refunds</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Refunds come with paid ticketing on the Pro plan, and a schedule that has since dropped back to Free can still return the money for sales it took. What the action does depends on how the sale was paid:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Stripe and PayPal:</strong> <strong class="text-gray-900 dark:text-white">Refund Ticket</strong> sends the money back through the provider, and the status here changes only once it has gone. A dialog shows <strong class="text-gray-900 dark:text-white">Available to refund</strong> and asks for a <strong class="text-gray-900 dark:text-white">Refund Amount</strong>, so you can return all of it or part.</li>
            <li><strong class="text-gray-900 dark:text-white">Every other method</strong> (Invoice Ninja, Payfast, a payment link, cash, or any sale you marked paid by hand) shows <strong class="text-gray-900 dark:text-white">Mark as Refunded</strong> instead. It records the refund and adjusts your revenue figures, and you return the money in your provider's own dashboard.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A partial refund leaves the sale paid and its tickets valid, and the row shows <strong class="text-gray-900 dark:text-white">Refunded so far</strong>. Refund the rest later and the sale becomes refunded, with its tickets and seats back on sale. Until then your <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> revenue still counts the sale in full.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A payment plan on <a href="#installments" class="doc-link">installments</a> can only be refunded in full: Refund Ticket returns each payment already collected, one at a time, and stops the rest.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Refund here, not in your Stripe or PayPal dashboard</div>
            <p>A refund you make in Stripe or PayPal directly is not reported back to Event Schedule. The sale stays paid, its revenue stays counted and its ticket keeps scanning at the door. Refund Stripe and PayPal sales from this page instead.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Four more things to know before you refund:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Refund first, cancel after.</strong> The refund action only appears while a sale is still paid. <strong class="text-gray-900 dark:text-white">Cancel Ticket</strong> and <strong class="text-gray-900 dark:text-white">Delete</strong> never move money, and once a sale is cancelled the money has to go back in your provider's own dashboard.</li>
            <li><strong class="text-gray-900 dark:text-white">A refund that cannot be confirmed is not retried.</strong> The sale is left for you to check against your provider's dashboard, with a warning on its row, because retrying a refund that may already have gone through is how one refund becomes two. A refund the provider actively rejects is different: nothing moved, so the amount is released and you can try again.</li>
            <li><strong class="text-gray-900 dark:text-white">The buyer is not emailed.</strong> Event Schedule does not tell the buyer about a refund, so tell them yourself if you want them to know.</li>
            <li><strong class="text-gray-900 dark:text-white">Webhooks.</strong> Mark Paid, a full refund and a cancellation fire the matching <x-link href="{{ route('marketing.docs.developer.webhooks') }}">webhook</x-link>: <code class="doc-inline-code">sale.paid</code>, <code class="doc-inline-code">sale.refunded</code> or <code class="doc-inline-code">sale.cancelled</code>. A partial refund does not fire one, because the sale is still paid.</li>
        </ul>

        <h4 id="currency-lock" class="text-base font-semibold text-gray-900 dark:text-white mb-2">The currency locks once an event has taken money</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-6">That means a sale that is paid, refunded or awaiting payment review. A sale records no currency of its own, so changing the event's currency afterwards would relabel its past sales and work a later refund out in the new currency. The event's Payment row greys out the <strong class="text-gray-900 dark:text-white">Currency</strong> selector with a note saying why, and the <x-link href="{{ route('marketing.docs.developer.api') }}">API</x-link> refuses the change too. Unpaid, cancelled and expired sales took no money, so they leave the currency open.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong>, choose the <strong class="text-gray-900 dark:text-white">Settings</strong> tab, open its <strong class="text-gray-900 dark:text-white">Notifications</strong> row and turn on <strong class="text-gray-900 dark:text-white">New ticket sale</strong>. Each owner and admin of the schedule opts in separately. If <a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">push notifications</a> are enabled, the same alert is mirrored to the browser.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Ongoing sale notifications are a Pro feature, but the <strong class="text-gray-900 dark:text-white">first paid sale on each event</strong> notifies you on every plan, including Free, as long as the toggle is on.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">On eventschedule.com, sale notification emails go out only once the schedule has its own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>, and they are sent from that address. Until then the <strong class="text-gray-900 dark:text-white">New ticket sale</strong> toggle stays greyed out, unless the schedule is on Pro and push notifications are set up: the push goes out without email settings, so the toggle stays usable for it. A selfhosted install only needs a working mailer. Every notification email includes an unsubscribe link.</p>
    </section>

    <!-- Export -->
    <section id="export" class="doc-section">
        <h3 class="doc-subheading">Exporting Sales Data <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Export your sales data for accounting, tax purposes, or to import into other systems. The export covers every schedule you own or administer.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong></li>
            <li>Narrow the list with the filter box, and turn on <strong class="text-gray-900 dark:text-white">Include past events</strong> if you need older sales. The export contains exactly what the list is showing, on every page of it.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Export</strong>, at the end of the filter row. A CSV file named after today's date downloads.</li>
        </ol>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">What the file holds</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One row per sale, and on an order with individual tickets one row per guest as well:</p>
        <ul class="doc-list mb-6">
            <li>Buyer name, email and phone</li>
            <li>Event, event date and purchase date</li>
            <li>Ticket types and quantities, and add-ons, as separate columns</li>
            <li>Amount and currency</li>
            <li>Promo code and discount amount, gift card code and gift card amount</li>
            <li>Transaction reference, payment method and status</li>
            <li><code class="doc-inline-code">Group ID</code>, shared by the guests of one order with individual tickets, and <code class="doc-inline-code">Order ID</code>, shared by the events of one <a href="#cart" class="doc-link">cart</a> purchase</li>
            <li>Check-in status and check-in time</li>
            <li>Pass type, visits used and expiry, for <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass</a> sales</li>
            <li>Seats, for <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated seating</a> sales</li>
            <li>Custom field responses, per order and per ticket type, one column per field</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The file starts with a byte order mark, so Excel opens it with accents and non-Latin names intact.</p>
    </section>

    <!-- Importing Attendees -->
    <section id="importing-attendees" class="doc-section">
        <h3 class="doc-subheading">Importing Attendees <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Bulk-add attendees who paid out-of-band (cash, sponsored, or through a third-party system) instead of checking them out through the public ticket page. Up to 5,000 attendees per import.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and click <strong class="text-gray-900 dark:text-white">Import</strong>, in the page's title row. The Import Attendees page has a <strong class="text-gray-900 dark:text-white">Sales</strong> link above its title to take you back</li>
            <li>Pick a schedule (if you manage more than one) and an event. On a recurring event, also pick the <strong class="text-gray-900 dark:text-white">Event Date</strong> the attendees are coming on</li>
            <li>Either type rows on the <strong class="text-gray-900 dark:text-white">Form Entry</strong> tab, using <strong class="text-gray-900 dark:text-white">+ Add attendee</strong> for each further row, or switch to <strong class="text-gray-900 dark:text-white">Upload CSV</strong></li>
            <li>When uploading, drop the file on the page, match each of its columns to a field under <strong class="text-gray-900 dark:text-white">Map Columns</strong> (or <strong class="text-gray-900 dark:text-white">Skip</strong> it), then click <strong class="text-gray-900 dark:text-white">Next</strong> to bring the rows into the form for review</li>
            <li>Optionally turn on <strong class="text-gray-900 dark:text-white">Send Email</strong> to send a confirmation email to each paid attendee</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save Attendees</strong>. You land back on the Sales page, which says how many were imported and how many rows were skipped</li>
        </ol>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Supported CSV columns</h4>
        <ul class="doc-list mb-6">
            <li>Name, Email (required), Phone</li>
            <li>Ticket Type (matched by name to existing ticket types)</li>
            <li>Quantity, Amount, Status (paid / unpaid)</li>
            <li>Any custom fields you have defined, on the event or on a ticket type</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Email is the only required column. Everything else auto-detects from the header name, and rows with no ticket type fall back to the type you picked. Comma, semicolon, and tab delimiters are all supported, as are UTF-8 CSVs exported from Excel. Duplicate emails within the same import are skipped automatically, as is any row that would push a ticket type past its remaining quantity.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Imports never take money. Imported attendees are recorded with their own payment method, shown as <em>Manual import</em> in the Sales list, so nothing is charged through Stripe or PayPal on the way in: the row is a record of a sale that already happened somewhere else.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">With <strong class="text-gray-900 dark:text-white">Send Email</strong> on, each attendee imported as paid gets the same confirmation email a checkout sends; unpaid rows are not emailed. On eventschedule.com it comes from our address until the schedule has its own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>, then from yours. A selfhosted install needs a working mailer, and without one the save button stays disabled while Send Email is on.</p>
    </section>

    <!-- Check-in -->
    <section id="check-in" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
            </svg>
            Check-in at the Door
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Use your phone to scan tickets at the door. No special hardware is needed, and scanning is on every plan, Free included, for every ticket and registration. The live <a href="#checkin-dashboard" class="doc-link">check-in dashboard</a> is the part that needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> on your phone and tap <strong class="text-gray-900 dark:text-white">Scan Ticket</strong>, at the end of the title row</li>
            <li>Allow the camera, and point it at the QR code on the ticket</li>
            <li>The result appears with the event, its date and the attendee's name, and the ticket is checked in</li>
            <li>Tap <strong class="text-gray-900 dark:text-white">Scan Another Ticket</strong> for the next person</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Scanning at event</strong> picker at the top of the scanner matters for <a href="{{ route('marketing.docs.subscriptions') }}#redeeming" class="doc-link">passes</a>, which work at several events. An ordinary ticket is always checked against its own event. The page's title row has a <strong class="text-gray-900 dark:text-white">Sales</strong> link back and a <strong class="text-gray-900 dark:text-white">Check-in</strong> button for the dashboard.</p>

        <h3 id="scan-results" class="doc-subheading">What the scanner shows</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Result</th>
                        <th>What it means</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-green-700 dark:text-green-400">Ticket Scanned Successfully!</span></td>
                        <td>Checked in. One scan admits every ticket on the order, each shown as a green square (carrying its seat on an <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated</a> event). With <a href="#options" class="doc-link">Individual tickets</a> each attendee has their own code.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-amber-700 dark:text-amber-400">Warning: This ticket has already been used</span></td>
                        <td>Shown under the result when the code was scanned before. The box turns orange, and so do the squares that were already checked in.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-amber-700 dark:text-amber-400">Overdue balance</span></td>
                        <td>An <a href="#installments-missed" class="doc-link">installment plan</a> is on hold. The screen names the attendee, the amount outstanding and how much has been paid. The scan does not check them in.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">Check-in opens 24 hours before the event starts.</span></td>
                        <td>It is too early. A ticket can be scanned from 24 hours before its date's start time.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">The check-in period for this event has ended.</span></td>
                        <td>The date is over: its start plus its duration, or two hours when no duration is set.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">This ticket is not paid</span><br><span class="font-semibold text-red-700 dark:text-red-400">This ticket is cancelled</span><br><span class="font-semibold text-red-700 dark:text-red-400">This ticket is refunded</span><br><span class="font-semibold text-red-700 dark:text-red-400">This ticket has expired</span><br><span class="font-semibold text-red-700 dark:text-red-400">This ticket is awaiting payment review</span></td>
                        <td>Only a paid order is admitted. A partial refund leaves the order paid, so its ticket keeps scanning.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">This ticket is not valid</span></td>
                        <td>The code matches no order on this event, or the order was deleted.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">You are not authorized to scan this ticket</span></td>
                        <td>The ticket is for an event on a schedule you are not a member of.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Any team member with access to your schedule can scan tickets, including viewers: have them log in on their own phone.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">On the buyer's side, the confirmation email and the ticket page show the QR code on every plan. An unpaid or cancelled order shows its QR code struck through, marked Unpaid or Void.</p>
    </section>

    <!-- Check-in Dashboard -->
    <section id="checkin-dashboard" class="doc-section">
        <h3 class="doc-subheading">Check-in Dashboard <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Watch arrivals at one event as tickets are scanned. Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose <strong class="text-gray-900 dark:text-white">Check-in</strong> in the title row, or use the <strong class="text-gray-900 dark:text-white">Check-in</strong> button on the scanner. The page is built for a phone at the door, with <strong class="text-gray-900 dark:text-white">Scan Ticket</strong> in its own title row. Top to bottom:</p>

        <x-doc-screenshot id="tickets--checkin" alt="The Check-in dashboard: the event and date pickers, three figures with their bar, Find someone at the door, and arrivals for each ticket type" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th>What it shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Event and date</td>
                        <td>Which event you are counting, and which of its dates. It opens on an event with sales today, otherwise your most recent. Events that sell tickets or take registrations on a Pro schedule are listed.</td>
                    </tr>
                    <tr>
                        <td>Three figures</td>
                        <td><strong class="text-gray-900 dark:text-white">Checked In</strong>, <strong class="text-gray-900 dark:text-white">Still to arrive</strong> and <strong class="text-gray-900 dark:text-white">Tickets Sold</strong>, with a progress bar and percentage under them. When a <a href="{{ route('marketing.docs.subscriptions') }}#admissions-per-event" class="doc-link">pass admits guests</a>, a headcount including guests sits under Checked In; when pass holders have booked ahead, <em>Seats reserved in advance</em> sits under Tickets Sold.</td>
                    </tr>
                    <tr>
                        <td>Find someone at the door</td>
                        <td>On an <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated seating</a> event, type a seat (C14), a name or an email to see who holds a seat and whether they have arrived. It only looks; admitting somebody is still done by scanning.</td>
                    </tr>
                    <tr>
                        <td>Ticket types</td>
                        <td>Checked in out of sold for each ticket type, with a bar. Shown when the event has more than one type.</td>
                    </tr>
                    <tr>
                        <td>Recent Check-ins</td>
                        <td>The last 10 arrivals, with name, ticket type, how long ago, and the seat on an allocated event.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Counts are keyed to the venue's own calendar date, so an evening event west of UTC reports correctly rather than rolling over at the wrong midnight. Only redemptions count as checked in: a pass holder who booked a seat in advance appears in the reserved count until they actually arrive. The figures refresh every 10 seconds while the tab is in the foreground, so a phone in a pocket is not drained.</p>
    </section>

    <!-- Wallet Passes -->
    <section id="wallet-passes" class="doc-section">
        <h3 class="doc-subheading">Wallet Passes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Ticket buyers can save their ticket into Google Wallet from the ticket page, from the order page of a multi-event purchase, or from the confirmation email. The pass carries the same QR code the ticket page shows, so it scans at the door exactly like any other ticket, and it works offline once saved.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Event details on the pass</strong> - name, venue, date and time, attendee name, ticket type and seat</li>
            <li><strong class="text-gray-900 dark:text-white">Arrival reminder</strong> - when the venue has map coordinates, Google can notify the attendee as they arrive nearby</li>
            <li><strong class="text-gray-900 dark:text-white">One pass per event</strong> - a multi-event order gets a separate pass for each event, since each is scanned with its own code. A single order for several people is one pass showing the number it admits, unless the event issues individual tickets</li>
            <li><strong class="text-gray-900 dark:text-white">Passes and registrations too</strong> - a <a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">pass or subscription</a> of any type saves as one undated pass rather than one per date, and shows how many people it admits at each event when that is more than one. If the pass has an expiry, Google Wallet archives it once that date passes. Free registrations get a wallet pass on the same terms as a paid ticket</li>
            <li><strong class="text-gray-900 dark:text-white">Not for every order</strong> - there is no wallet button for an appointment booking, on an event you have cancelled, or while an <a href="#installments" class="doc-link">installment plan</a> is behind on its payments</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">At the door a wallet pass is scanned exactly like any other ticket: the attendee opens it and you scan it from <a href="#check-in" class="doc-link">Scan Ticket</a> as usual. On an <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated</a> event the seat is printed on the pass.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The pass is a snapshot taken when the attendee saves it. Cancelling or fully refunding an order does not remove a pass already on someone's phone, but the code stops working: the door scanner checks the order's live status, and a cancelled or refunded ticket is refused there just as it is on the ticket page. A partial refund leaves the order paid, so its pass keeps working.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Turned on by the operator</div>
            <p>Wallet passes need a Google Wallet issuer account, so the button appears only once the person running the installation has connected one. On a selfhosted install that is a one-time <a href="{{ route('marketing.docs.selfhost.google_wallet') }}" class="doc-link">setup step</a>; with nothing configured, no button is shown and nothing is sent to Google. It is free on every plan. There is no per-schedule setting: if you are unsure whether it is on, open a paid ticket and look for the badge.</p>
        </div>
    </section>

    <!-- Waitlist -->
    <section id="waitlist" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Waitlist
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When an event date fills up, fans can join a waitlist to be notified when spots become available.</p>

        <div class="doc-callout doc-callout-plan mb-6">
            <div class="doc-callout-title">Free for registration, Pro for tickets</div>
            <p>The waitlist on a full <a href="#registration" class="doc-link">Free registration</a> date works on every plan. The waitlist on a sold-out <em>ticketed</em> event needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above <x-doc-badge plan="pro" />.</p>
        </div>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>When all tickets sell out for an event date, a <strong class="text-gray-900 dark:text-white">Join Waitlist</strong> button appears on the event page</li>
            <li>Guests enter their name and email</li>
            <li>When a spot opens up (a sale is cancelled, fully refunded or expires unpaid, a pass holder cancels a booked date, or your box office releases a seat), the next person in line is notified by email. A partial refund frees no spot</li>
            <li>They receive a link that is valid for 24 hours</li>
            <li>If they don't purchase in time, the next person in line is notified</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Only one person is notified at a time, to prevent overselling. The next is notified only after the current person's 24-hour window expires or they complete their purchase.</p>

        <h3 class="doc-subheading">Managing the Waitlist</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose the <strong class="text-gray-900 dark:text-white">Waitlist</strong> tab. The tab is there while at least one person is waiting or has been notified, and carries their count.</p>
        <ul class="doc-list mb-6">
            <li>Each row shows the person's name with their email under it, the event, the date they are waiting for, a status and when they joined</li>
            <li>The status is <strong class="text-gray-900 dark:text-white">Waiting</strong>, <strong class="text-gray-900 dark:text-white">Notified</strong> (their 24 hours are running), <strong class="text-gray-900 dark:text-white">Purchased</strong> or <strong class="text-gray-900 dark:text-white">Expired</strong></li>
            <li><strong class="text-gray-900 dark:text-white">Remove</strong>, at the end of a row, takes that person off the list after asking you to confirm</li>
            <li>Click a column heading to sort, and turn on <strong class="text-gray-900 dark:text-white">Include past events</strong> to see dates that have passed</li>
        </ul>
    </section>

    <!-- Interest List -->
    <section id="interest-list" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Interest List
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Most people who look at an event page are not ready to buy that minute. The interest list lets them leave an email address and hear from you when it matters, with no account and no sign-up.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the schedule's edit page, turn on <strong class="text-gray-900 dark:text-white">Show &ldquo;Notify Me&rdquo; Card</strong> under <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Settings &rarr; Advanced</a>. It is off until you do. The switch that counts is the one on the schedule that created the event, and it applies wherever that event is listed</li>
            <li>On a public event page, a visitor opens the <strong class="text-gray-900 dark:text-white">Add to Calendar</strong> menu and chooses <strong class="text-gray-900 dark:text-white">Tell me when tickets go on sale</strong>, or <strong class="text-gray-900 dark:text-white">Tell me if anything changes</strong> once tickets are on sale. An event that is already selling tickets or taking registrations also shows a <strong class="text-gray-900 dark:text-white">Not buying today? Tell me if anything changes</strong> link beside the buy button</li>
            <li>They type an email address and press <strong class="text-gray-900 dark:text-white">Notify me</strong>. No name, no account and no confirmation email to click</li>
            <li>Three emails go out automatically: one when that date's tickets go on sale, a reminder about 48 hours before it starts, and a cancellation notice if you cancel the event. Someone who asks once tickets are already on sale skips the first one</li>
            <li>A change reaches them only if you send it. When you save a new date or time on a one-off event, or a new venue or online link on any event, the editor asks <strong class="text-gray-900 dark:text-white">Notify attendees of this change?</strong>, and the list is emailed only if you choose <strong class="text-gray-900 dark:text-white">Notify attendees</strong></li>
            <li>Every message carries a one-click unsubscribe, and unsubscribing deletes the address rather than keeping it on a suppression list</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Each date of a recurring event keeps its own list, so someone who asks about one Friday hears about that Friday's tickets and that Friday's reminder. Nothing else is sent: the list never receives your newsletters or news of your other events.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Turning the card off again removes it and every link to it, and stops new sign-ups. Anyone already on a list still gets the emails they asked for.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The interest list is free on every plan and is not counted against your <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletter allowance</a>. It exists to help you find out whether anyone wants tickets before you go to the trouble of selling them.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On eventschedule.com, once more than 50 people are waiting, these emails go out only if the schedule has its own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a> or its owner has verified a phone number. A selfhosted install needs a working mailer.</p>

        <h3 class="doc-subheading">Seeing Who Is Waiting</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The event's <strong class="text-gray-900 dark:text-white">Tickets</strong> tab says how many people asked, for example "12 people asked to be told when tickets go on sale", counting each address once across every date. The line sits above the ticket types, so it shows once <strong class="text-gray-900 dark:text-white">Sell tickets</strong> is pressed. While a schedule has upcoming events and no ticket type, the dashboard suggestion reads "12 people are waiting to buy - add a ticket type". You see how many, not who, and neither number is shown publicly.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Not the same as followers or the waitlist</div>
            <p>Asking about one event is not subscribing to your schedule. Those people hear about that event and nothing else. Someone who wants everything you publish can <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">sign up to your schedule</a> or subscribe to its <a href="{{ route('marketing.docs.sharing') }}#calendar-feeds" class="doc-link">calendar feed</a>. The interest list is also not the <a href="#waitlist" class="doc-link">waitlist</a>: it is for a date that is not on sale yet, or a visitor who is not ready to buy, while the waitlist is for a date that has sold out.</p>
        </div>
    </section>

    <!-- Post-Event Feedback -->
    <section id="feedback" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            Post-Event Feedback <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Automatically collect ratings and comments from attendees after your events end. Feedback emails are sent to ticket buyers and RSVP attendees, linking to a simple form where they can rate their experience.</p>

        <h3 class="doc-subheading">Enabling Feedback</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> and choose the <strong class="text-gray-900 dark:text-white">Engagement</strong> tab</li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Feedback</strong> row</li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">Post-event feedback</strong></li>
            <li>Under <strong class="text-gray-900 dark:text-white">Send feedback request after</strong>, choose how long after the event ends the emails go out: 1, 2, 6, 12, 24 or 48 hours. The default is 24</li>
            <li>Optionally turn on <strong class="text-gray-900 dark:text-white">Show feedback publicly</strong> to display ratings and comments on the event page, and use <strong class="text-gray-900 dark:text-white">Send test feedback email</strong> to send yourself the request</li>
            <li>Save your changes</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">On eventschedule.com the switch stays disabled until the schedule has its own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a> configured, since feedback requests are sent from your address rather than ours. A selfhosted install only needs a working mailer.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">One event can differ from its schedule. On the event's edit page, open the <strong class="text-gray-900 dark:text-white">Engagement</strong> tab and its <strong class="text-gray-900 dark:text-white">Feedback</strong> row, and choose <strong class="text-gray-900 dark:text-white">Enabled</strong> or <strong class="text-gray-900 dark:text-white">Disabled</strong> to override, or <strong class="text-gray-900 dark:text-white">Same as schedule</strong> to follow the schedule's setting.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>After an event ends and the configured delay passes, feedback request emails are automatically sent to attendees. The queue is checked hourly, so times are approximate</li>
            <li>Each email contains a link to a feedback form branded with your schedule's logo and colors</li>
            <li>Attendees rate their experience from 1 to 5 stars and can leave an optional comment</li>
            <li>Each attendee can only submit feedback once</li>
        </ol>

        <h3 id="feedback-tab" class="doc-subheading">Viewing Feedback</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose the <strong class="text-gray-900 dark:text-white">Feedback</strong> tab. Four figures run across the top: <strong class="text-gray-900 dark:text-white">Pending</strong> (with when the next batch goes out), <strong class="text-gray-900 dark:text-white">Sent</strong> and awaiting a response, <strong class="text-gray-900 dark:text-white">Responded</strong> (with the average rating) and the <strong class="text-gray-900 dark:text-white">Response Rate</strong>. Under them, the three stages a request passes through:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it lists</th>
                        <th>What you can do</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Pending Feedback Emails</td>
                        <td>Requests still to be sent, by event, with the number of attendees and an estimated send time. Open a row to see who.</td>
                        <td><strong class="text-gray-900 dark:text-white">Send now</strong> sends the ones that are ready without waiting for the hourly run. <strong class="text-gray-900 dark:text-white">Cancel all</strong> calls the queue off.</td>
                    </tr>
                    <tr>
                        <td>Sent - Awaiting Response</td>
                        <td>Attendees who were emailed and have not replied, with when the request went out.</td>
                        <td><strong class="text-gray-900 dark:text-white">Resend</strong> sends one attendee the request again, if it was missed or landed in spam.</td>
                    </tr>
                    <tr>
                        <td>Responses</td>
                        <td>Each answer: attendee, event, date, star rating, the whole comment and when it was submitted. The headings sort.</td>
                        <td><strong class="text-gray-900 dark:text-white">Export Feedback</strong> downloads the responses as a CSV file.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The first two cards only appear while they have something in them. A sale with an answer also shows its star rating beside its status in the <a href="#sales-list" class="doc-link">Sales list</a>.</p>

        <h3 class="doc-subheading">Feedback Notifications</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">To receive an email when new feedback is submitted, turn on <strong class="text-gray-900 dark:text-white">New feedback</strong> in the <strong class="text-gray-900 dark:text-white">Notifications</strong> row of the schedule's <strong class="text-gray-900 dark:text-white">Settings</strong> tab. Each notification includes the event name, attendee name, star rating, and comment.</p>
    </section>

    <!-- Financial Information -->
    <section id="financial" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Financial Information
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">What happens to the money around a sale: refunds, taxes, fees, cancelled events and payouts.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Refunds</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Refunding a Stripe or PayPal sale on the Sales page sends the money back through the provider, in full or in part, and then updates the sale here. Every other method (Invoice Ninja, Payfast, a payment link or cash) is recorded here with Mark as Refunded, and you process the money in that provider's own dashboard. A Payfast reference is shown as plain text rather than a link, so you will need to search for it in your Payfast dashboard. Stripe refunds appear on customer statements within 5-10 business days. See <a href="#refunds" class="doc-link">Refunds</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Taxes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Event Schedule does not automatically calculate or collect sales tax. Set your ticket prices inclusive of any applicable taxes. For tax reporting and your own records, <a href="#export" class="doc-link">export your sales data</a> from the Sales page. Consult a tax professional for your specific obligations.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Processing Fees</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stripe charges their standard processing fees (typically 2.9% + $0.30 per transaction in the US). These fees are deducted from your payouts. Event Schedule adds no platform fee on any plan, Free included.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled or Deleted Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An event with any sales cannot be deleted: the app asks you to cancel it instead, so buyers keep their records. <strong class="text-gray-900 dark:text-white">Cancel event</strong> keeps every sale and refund record, stops any remaining installment payments, and emails the people there are to tell: ticket holders and registrants (on eventschedule.com, only when the schedule has its own email settings) and anyone on the <a href="#interest-list" class="doc-link">interest list</a>. It does not refund anyone, and the sales stay paid, so refund them from the Sales page. A cancelled event can be restored later.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payout Schedule</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stripe pays out on a rolling basis (typically 2 business days in the US, varies by country). View your payout schedule and history in your Stripe Dashboard. Invoice Ninja follows your configured payment terms.</p>
            </div>
        </div>

    </section>

    <!-- Embed Widget -->
    <section id="embed-widget" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Embed Widget
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Embed a ticket purchase or RSVP form directly on your own website using an iframe. Visitors can buy tickets or register without leaving your site.</p>

        <div class="doc-callout doc-callout-plan mb-6">
            <div class="doc-callout-title">RSVP embed is free; the ticket embed is Pro</div>
            <p>The <code class="doc-inline-code">rsvp=true</code> widget works on every plan, including Free. The <code class="doc-inline-code">tickets=true</code> widget needs <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro</a> or above <x-doc-badge plan="pro" />.</p>
        </div>

        <h3 class="doc-subheading">Getting the Embed Code</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your event for editing and go to the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab</li>
            <li>Press <strong class="text-gray-900 dark:text-white">Sell tickets</strong> or <strong class="text-gray-900 dark:text-white">Free registration</strong> and save the event</li>
            <li>Click the <strong class="text-gray-900 dark:text-white">Embed Tickets</strong> (or <strong class="text-gray-900 dark:text-white">Embed Registration</strong>) link at the end of the tab's title row</li>
            <li>Copy the iframe code and paste it into your website's HTML</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The link that opens the snippet only appears on a saved event of a Pro schedule. On the Free plan you can still embed the RSVP form by building the URL yourself from the parameters below.</p>

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

        <h3 class="doc-subheading">What to Expect</h3>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">Payment leaves the frame.</strong> Every payment method except cash sends the buyer out of the iframe to pay: Stripe, PayPal, Payfast, Invoice Ninja and a payment link all open in the full browser window, since payment pages generally refuse to load inside another site's frame. Stripe, PayPal and Payfast bring the buyer back to their ticket page afterwards. Cash and free ticket checkouts complete inside the embed.</li>
            <li><strong class="text-gray-900 dark:text-white">Hidden events do not embed.</strong> The Tickets tab does not offer the embed link on an <strong class="text-gray-900 dark:text-white">Unlisted</strong> event, and Draft and Internal events are open to schedule members only. A password-protected event only embeds for someone who has already entered the password.</li>
            <li><strong class="text-gray-900 dark:text-white">One event per widget.</strong> The <a href="#cart" class="doc-link">multi-event cart</a> is not offered inside an embed.</li>
        </ul>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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
                    "text": "Edit your event, open the Tickets tab, press Sell tickets, and add a type with a price and quantity.",
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
