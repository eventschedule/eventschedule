<x-docs-page
    key="gift-cards"
    description="Sell prepaid gift cards for your events: set the denominations and currency, let buyers email a card to someone else, and redeem it at checkout."
    lede="Let anyone buy a gift card for someone else. The recipient gets a code by email and redeems the balance toward tickets for your events."
    article-description="How to sell gift cards for your events: set denominations, let buyers send a card by email, and redeem the balance toward tickets at checkout."
    plan="pro"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">What is a gift card?</x-doc-nav-link>
        <x-doc-nav-link href="#setup">Step 1 - Enable gift cards</x-doc-nav-link>
        <x-doc-nav-link href="#buying">Step 2 - A customer buys one</x-doc-nav-link>
        <x-doc-nav-link href="#redeeming">Step 3 - Redeem at checkout</x-doc-nav-link>
        <x-doc-nav-link href="#managing">Step 4 - Track &amp; manage</x-doc-nav-link>
        <x-doc-nav-link href="#good-to-know">Good to know</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            What is a gift card?
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">gift card</strong> is a prepaid voucher one person buys as a present for someone else. The recipient is emailed a 12 character code and spends the balance on tickets for your events.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">You</strong> choose the amounts you sell (for example 25, 50, 100), the currency, and how buyers pay.</li>
            <li><strong class="text-gray-900 dark:text-white">A buyer</strong> picks one of your amounts, enters the recipient's name and email plus an optional message, and pays.</li>
            <li><strong class="text-gray-900 dark:text-white">The recipient</strong> gets the card by email and enters its code at checkout to take the balance off their ticket order.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The balance carries over</div>
            <p>Gift cards are balance-tracked, not single-use. If an order costs less than the card, the remainder stays on the code for next time. If it costs more, the card covers what it can and the customer pays the difference with the event's normal payment method.</p>
        </div>
        <h3 class="doc-subheading">Where a card can be spent</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A card belongs to the schedule that sold it. At checkout it is accepted on an event when all three of these are true:</p>
        <ul class="doc-list mb-6">
            <li>The event is <strong class="text-gray-900 dark:text-white">on that schedule</strong>, including events you add after the card was sold.</li>
            <li>The event belongs to the <strong class="text-gray-900 dark:text-white">same account</strong> that sold the card. A curator schedule listing another organizer's event cannot take your card, because the payout goes to them.</li>
            <li>The event's ticket currency <strong class="text-gray-900 dark:text-white">matches the card's currency</strong>.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Redemption happens in the ticket checkout only. Appointment bookings have no gift card field, so a card cannot pay for a booked time slot.</p>
        <p class="text-gray-600 dark:text-gray-300">Selling gift cards is a <strong class="text-gray-900 dark:text-white">Pro</strong> feature, and is included on all selfhosted deployments. Cards you have already sold stay redeemable even if you later turn selling off or your plan lapses.</p>
    </section>

    <!-- Setup -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Step 1 - Enable gift cards
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Gift cards are set up per schedule, so each schedule has its own amounts, currency and codes.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit</strong>.</li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Gift Cards</strong> section in the sidebar.</li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">Enable gift cards</strong>.</li>
            <li>Fill in the settings below, then <strong class="text-gray-900 dark:text-white">Save</strong>.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On a free schedule the section shows an upgrade prompt instead of the settings, because selling gift cards requires <strong class="text-gray-900 dark:text-white">Pro</strong>.</p>

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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Enable gift cards</span></td>
                        <td>Lets customers buy gift cards for this schedule. Turning it off later hides the purchase page but never blocks cards that were already sold.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Gift card amounts</span></td>
                        <td>The denominations buyers can choose. Add up to 12, each above zero and no more than 99,999. Buyers pick one of your amounts and cannot type their own, so nothing is on sale until you save at least one.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Currency</span></td>
                        <td>Cards can only be redeemed at events priced in this currency. It defaults to the currency of your most recent ticketed event, or the platform currency if you have not priced one yet.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Valid for (days)</span></td>
                        <td>How long a card stays usable, counted from the moment payment is confirmed rather than from purchase. Anything from 1 to 3650 days. Leave it empty and cards never expire.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Payment Method</span></td>
                        <td>How buyers pay: Cash, Stripe, Invoice Ninja, or your payment link. Only the methods already set up on your account are listed, and Cash is the default. Payfast cannot be used for gift cards even when it is connected.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Before the purchase page goes live</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You can save the settings at any time, but the purchase page returns a 404 and the buy buttons stay hidden until every one of these is true:</p>
        <ul class="doc-list mb-6">
            <li>The schedule is on <strong class="text-gray-900 dark:text-white">Pro</strong> (or the install is selfhosted).</li>
            <li><strong class="text-gray-900 dark:text-white">Enable gift cards</strong> is on and at least one amount is saved.</li>
            <li>The payment method you picked is actually connected. Use <strong class="text-gray-900 dark:text-white">Manage payment methods</strong> under the list to connect Stripe, add your Invoice Ninja key, or set a payment link. Cash needs nothing.</li>
            <li>On eventschedule.com only, the schedule has its own email settings.</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Hosted schedules need email settings</div>
            <p>The recipient's email <em>is</em> the delivery mechanism, so on eventschedule.com a schedule must have its own SMTP host and username saved under <strong class="text-gray-900 dark:text-white">Edit &rarr; Integrations &rarr; Email Settings</strong> before gift cards go live. Selfhosted installations use the server's mail configuration instead, so there is nothing extra to do. See <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>.</p>
        </div>

        <div class="doc-callout doc-callout-tip mb-2">
            <div class="doc-callout-title">Your purchase link</div>
            <p>Once selling is live, a <strong class="text-gray-900 dark:text-white">Gift card purchase link</strong> appears at the bottom of the section with a <strong class="text-gray-900 dark:text-white">Copy</strong> button. Share it in a newsletter, a social post, or anywhere you would put a ticket link.</p>
        </div>
    </section>

    <!-- Buying -->
    <section id="buying" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            Step 2 - A customer buys one
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">While selling is live, two entry points appear on your public pages: a <strong class="text-gray-900 dark:text-white">Gift Cards</strong> button in your schedule's header, and a <strong class="text-gray-900 dark:text-white">Gift cards available - buy one</strong> link under the ticket selector on your event pages. Both disappear the moment selling is switched off. On the purchase page the buyer:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Picks one of your amounts. The first one is preselected.</li>
            <li>Enters their own name and email.</li>
            <li>Enters the <strong class="text-gray-900 dark:text-white">recipient's</strong> name and email, and an optional personal message of up to 500 characters. Ticking <strong class="text-gray-900 dark:text-white">Send to myself</strong> hides those three fields and buys credit for the buyer's own use.</li>
            <li>Presses <strong class="text-gray-900 dark:text-white">Buy Gift Card</strong> and pays with whichever method you chose.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Before they pay, the page tells them how the card is delivered, whether it expires (and after how many days), and that it works for any event on your schedule.</p>

        <h3 class="doc-subheading">Activation and emails</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A card is only activated once its payment is confirmed: Stripe and Invoice Ninja activate it from their payment notification, a payment link activates it when the buyer returns, and a cash card waits for you. Activation is what starts the expiry clock and sends the emails:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">The recipient</strong> gets the card itself: the amount, the code, the buyer's message, the expiry date if there is one, and a link to a private card page.</li>
            <li><strong class="text-gray-900 dark:text-white">The buyer</strong> gets a receipt with the code as a backup. This one is skipped when the buyer's address and the recipient's address are the same, since it would be the same inbox.</li>
            <li><strong class="text-gray-900 dark:text-white">You</strong> get a sale notification. It goes to every owner and admin of the schedule who has turned on <strong class="text-gray-900 dark:text-white">New ticket sale</strong> under <strong class="text-gray-900 dark:text-white">Edit &rarr; Settings &rarr; Notifications</strong>. That toggle is off by default and each team member controls their own.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-2">
            <div class="doc-callout-title">Paying by cash</div>
            <p>A cash card is created as <strong class="text-gray-900 dark:text-white">Pending Payment</strong> and nothing is emailed yet. Collect the money, then use <strong class="text-gray-900 dark:text-white">Mark Paid</strong> on the Sales page to activate it and send the code. Cash cards are never cancelled automatically. An unpaid Stripe, Invoice Ninja or payment link purchase is cancelled for you 48 hours after it was started, so abandoned checkouts do not pile up.</p>
        </div>
    </section>

    <!-- Redeeming -->
    <section id="redeeming" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Step 3 - Redeem at checkout
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The ticket checkout gets a <strong class="text-gray-900 dark:text-white">Gift Card</strong> field. It appears only when a schedule on the event, owned by the same account, has at least one card that is still active, still in credit and not expired. When the event also has a live promo code, the two fields sit together under the heading <strong class="text-gray-900 dark:text-white">Have a discount or gift card code?</strong>, with the gift card second.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>The customer opens the event and chooses their tickets.</li>
            <li>They type the code into <strong class="text-gray-900 dark:text-white">Gift Card</strong> and press <strong class="text-gray-900 dark:text-white">Apply</strong>. Lower case, spaces and dashes are all fine.</li>
            <li>The code is checked straight away, and they see how much is applied and how much will be left on the card.</li>
            <li>They finish checkout and pay whatever is still owed.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The card comes off <em>after</em> any volume discount and promo code, so it is applied to the already discounted total:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Order costs less than the card:</strong> the difference stays on the card for a future order.</li>
            <li><strong class="text-gray-900 dark:text-white">Order costs more:</strong> the card covers what it can and the customer pays the rest with the event's payment method.</li>
            <li><strong class="text-gray-900 dark:text-white">Card covers the whole order:</strong> there is nothing left to pay, so the order is marked paid immediately and the ticket is emailed.</li>
        </ul>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Why a code can be refused</div>
            <p>A code is rejected when it is not yet paid for, cancelled, refunded, expired, out of balance, in a different currency from the event (the message names both), or when the event belongs to a different account. Unlike a promo code, an unusable gift card stops the checkout rather than quietly charging full price, because the customer is expecting it to pay for the order.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Stripe's minimum charge</div>
            <p>Stripe will not process a charge below 50 of the currency's smallest units (0.50 USD or EUR, 50 JPY). Payfast has its own floor of R5.00. On an event taking payment through either, if applying the whole balance would leave less than that still to pay, slightly less is applied and the small remainder stays on the card, so checkout never lands on an amount the gateway would refuse. Events paid by cash, Invoice Ninja or a payment link have no floor and are not adjusted.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-2">
            <div class="doc-callout-title">An abandoned checkout gives the balance back</div>
            <p>The balance is deducted when the order is created, so an unpaid order would otherwise hold it. If the customer walks away without paying online, that order is expired 48 hours later and the deducted amount goes back onto the card automatically. Cash orders are skipped by that cleanup, since those are settled with you in person, so cancel one yourself if the customer never turns up (or set <strong class="text-gray-900 dark:text-white">Expire unpaid tickets</strong> on the event to release them on your own schedule).</p>
        </div>
    </section>

    <!-- Managing -->
    <section id="managing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Step 4 - Track &amp; manage
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose the <strong class="text-gray-900 dark:text-white">Gift Cards</strong> tab, which carries a count of the cards you have sold. It lists every card sold across all the schedules you own, newest first, with a running total of the balance still outstanding on active cards (one total per currency). Each row shows the code, the recipient, the purchase date, the remaining balance against the original value, and a status pill.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Expand a row to see the buyer, the payment method, the expiry date, which schedule sold it, the personal message, and a table of every order the card was redeemed against. Orders that used a card are also flagged with a <strong class="text-gray-900 dark:text-white">Gift Card</strong> line in the sales list and on the attendee's ticket, and the CSV export adds <strong class="text-gray-900 dark:text-white">Gift Card</strong> and <strong class="text-gray-900 dark:text-white">Gift Card Amount</strong> columns.</p>

        <h3 class="doc-subheading">Actions</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>What it does</th>
                        <th>Available when</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">View Gift Card</span></td>
                        <td>Opens the same card page the recipient sees, with the live balance and code.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Mark Paid</span></td>
                        <td>Activates the card, starts its validity period and emails the code.</td>
                        <td>Pending Payment, Payment Review</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Resend Email</span></td>
                        <td>Sends the card to the recipient again if they lost it. Only the recipient is emailed, not the buyer.</td>
                        <td>Active</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Refund</span></td>
                        <td>Stops the card from being redeemed and records it as refunded.</td>
                        <td>Active</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Cancel</span></td>
                        <td>Stops the card from being redeemed, for example a cash card that was never paid for.</td>
                        <td>Pending Payment, Active, Payment Review</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Refund and Cancel are one-way: neither can be undone, and a card cannot be reactivated or topped up afterwards. To give a customer more credit, sell them another card.</p>

        <h3 class="doc-subheading">Statuses</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Active</span></td>
                        <td>Paid for and redeemable.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Pending Payment</span></td>
                        <td>Bought but not paid for yet, so nothing has been emailed. Normal for cash cards.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Payment Review</span></td>
                        <td>A payment arrived but its amount or currency did not match the card, so it was not activated. Check the charge with your provider, then <strong class="text-gray-900 dark:text-white">Mark Paid</strong> if it was correct, or <strong class="text-gray-900 dark:text-white">Cancel</strong> if it was not.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Used Up</span></td>
                        <td>The balance reached zero. Nothing to do.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Expired</span></td>
                        <td>Past its validity date. Any leftover balance can no longer be spent.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Cancelled</span></td>
                        <td>Cancelled by you, by the buyer abandoning payment, or automatically 48 hours after an unpaid online purchase.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Refunded</span></td>
                        <td>You refunded it. Past redemptions are kept on the record.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Used Up and Expired are worked out from the balance and the expiry date rather than being set by hand, so a card flips to them on its own.</p>

        <div class="doc-callout doc-callout-warning mb-2">
            <div class="doc-callout-title">Refunds move money manually</div>
            <p>Marking a card refunded or cancelled stops it working, but it does not move any money in Stripe, Invoice Ninja or your bank. Do that yourself. And when an order that used a card is cancelled or refunded, the redeemed amount only goes back on the card while the card itself is still <strong class="text-gray-900 dark:text-white">Active</strong>: a card you have already cancelled or refunded is never credited back.</p>
        </div>
    </section>

    <!-- Good to know -->
    <section id="good-to-know" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Good to know
        </h2>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">One code, reused.</strong> A card works over and over until its balance runs out or it expires.</li>
            <li><strong class="text-gray-900 dark:text-white">The codes are easy to read out.</strong> Twelve characters, shown in groups of four, using only capital letters and digits that cannot be confused (no O, I, 0 or 1). Entry ignores case, spaces and dashes.</li>
            <li><strong class="text-gray-900 dark:text-white">Every card has its own page.</strong> The link in the recipient's email, and <strong class="text-gray-900 dark:text-white">View Gift Card</strong> in the admin panel, open a private page showing the live remaining balance, the code with a <strong class="text-gray-900 dark:text-white">Copy</strong> button, the expiry date, the message and how to redeem it.</li>
            <li><strong class="text-gray-900 dark:text-white">Expiry starts at payment.</strong> The validity period runs from the moment the card is activated, so a cash card waiting to be paid for does not quietly burn its days.</li>
            <li><strong class="text-gray-900 dark:text-white">Expiry is shown up front.</strong> If you set a validity period, buyers see it before they pay, and the date is printed on the card email and page.</li>
            <li><strong class="text-gray-900 dark:text-white">One schedule per card.</strong> Amounts, currency and codes belong to the schedule that sold the card. A card from one of your schedules cannot be spent on another.</li>
            <li><strong class="text-gray-900 dark:text-white">Tickets only.</strong> A card is redeemed in the ticket checkout. There is no gift card field on appointment bookings.</li>
            <li><strong class="text-gray-900 dark:text-white">Buy for yourself.</strong> The <strong class="text-gray-900 dark:text-white">Send to myself</strong> option makes it easy to top up your own credit.</li>
            <li><strong class="text-gray-900 dark:text-white">Redemption keeps working.</strong> Even if you turn selling off or your plan lapses, already-sold cards can still be redeemed.</li>
            <li><strong class="text-gray-900 dark:text-white">Deleting a schedule voids its cards.</strong> If any card still has a balance you are warned first, because deleting the schedule voids them permanently.</li>
            <li><strong class="text-gray-900 dark:text-white">Plan.</strong> Selling gift cards requires a <strong class="text-gray-900 dark:text-white">Pro</strong> plan, and is included on selfhosted deployments.</li>
        </ul>
    </section>

    <!-- See also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - set up ticketing, payment methods, and ticket types</li>
            <li><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> - sell a multi-use pass across many events</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - add the events your gift cards can be spent on</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">Email Settings</a> - required on hosted schedules before gift cards can be delivered</li>
        </ul>
    </section>
</x-docs-page>
