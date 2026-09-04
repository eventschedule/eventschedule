<x-docs-page
    key="subscriptions"
    description="Sell one pass a guest buys once and reuses across many events. Set up visit passes, memberships, festival passes, and season passes, then redeem and track them."
    lede="Sell one pass that a guest pays for once and reuses across many of your events - like a class pack, a membership, or a festival wristband."
    article-description="How to sell a multi-use pass or subscription: one purchase, one QR code, valid across many events. Includes setup, redeeming at the door, and usage tracking."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">What is a subscription?</x-doc-nav-link>
        <x-doc-nav-link href="#how-it-works">How it works</x-doc-nav-link>
        <x-doc-nav-link href="#example">A worked example</x-doc-nav-link>
        <x-doc-nav-group label="Step 1 - Create the pass" href="#setup" expanded>
            <x-doc-nav-link href="#types">Subscription types</x-doc-nav-link>
            <x-doc-nav-link href="#coverage">Covered events</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#buying">Step 2 - Buyers purchase</x-doc-nav-link>
        <x-doc-nav-group label="Advance booking" href="#advance-booking">
            <x-doc-nav-link href="#cancellation-policy">Cancellation policy</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#admissions-per-event">Admissions per event</x-doc-nav-link>
        <x-doc-nav-group label="Step 3 - Scan at the door" href="#redeeming">
            <x-doc-nav-link href="#scan-results">What the scanner shows</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#monitoring">Step 4 - Track usage</x-doc-nav-link>
        <x-doc-nav-link href="#good-to-know">Good to know</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See also</x-doc-nav-link>
    </x-slot:toc>

    <!-- What is a subscription -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            What is a subscription?
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">subscription</strong> (also called a <strong class="text-gray-900 dark:text-white">pass</strong>) is a special ticket your guest pays for <strong class="text-gray-900 dark:text-white">once</strong> and then reuses to get into <strong class="text-gray-900 dark:text-white">several of your events</strong> with a single QR code. Think of it like a gym membership, a 10-class punch card, or a festival wristband.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">It helps to compare it to the two things you may already know:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">A normal ticket</strong> gets one person into <em>one</em> event.</li>
            <li><strong class="text-gray-900 dark:text-white">A subscription</strong> gets one person into <em>many</em> events - you decide how many visits it's worth and which events it covers.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Two different things are called a subscription</div>
            <p><strong class="text-gray-900 dark:text-white">This page is about the pass you sell to guests.</strong> The buyer pays once. Event Schedule never bills them again - a pass here is a multi-use ticket, not an auto-renewing card on file. When it runs out of visits or expires, they simply buy another.</p>
            <p class="mt-2"><strong class="text-gray-900 dark:text-white">Your own plan is also called a subscription.</strong> That is what you pay Event Schedule for Pro or Enterprise, and it is managed on the <a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">Plan tab</a> of your schedule, not here. Nothing on this page changes your billing.</p>
        </div>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Passes need a Pro plan</div>
            <p><x-doc-badge plan="pro" /> <a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling tickets</a> is available on every plan: the free plan sells up to 25 paid tickets per calendar month, and Pro lifts that cap. Scanning a QR code at the door is on every plan too. The pass switch is the part that needs <strong class="text-gray-900 dark:text-white">Pro</strong>.</p>
            <p class="mt-2">The plan is checked when the pass is used, not only when it is sold. If a schedule drops back to the free plan its pass tickets keep every setting and the scanner still checks holders in, but they can no longer book dates in advance. A selfhosted install counts as Enterprise, so nothing on this page is held back there.</p>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            How it works
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">There are four stages, and the rest of this page walks through each one:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">You create the pass</strong> - add a ticket type, turn on the pass switch, then choose how many visits it's worth and which events it covers.</li>
            <li><strong class="text-gray-900 dark:text-white">A guest buys it once</strong> and receives a single QR code.</li>
            <li><strong class="text-gray-900 dark:text-white">Your staff scan the QR</strong> at each event - the first scan of the day at an event records one visit.</li>
            <li><strong class="text-gray-900 dark:text-white">You watch the usage</strong> on the <strong class="text-gray-900 dark:text-white">Subscriptions</strong> tab of the Sales page.</li>
        </ol>
        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-2">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-0">Buy once &rarr; scan at Event A <span class="text-gray-600 dark:text-gray-400">(visit 1)</span> &rarr; scan at Event B <span class="text-gray-600 dark:text-gray-400">(visit 2)</span> &rarr; &hellip; until the visit limit or the expiry date is reached.</p>
        </div>
    </section>

    <!-- Worked example -->
    <section id="example" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
            A worked example
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Meet <strong class="text-gray-900 dark:text-white">Maria</strong>, who runs a yoga studio with classes most days. She wants to sell a <strong class="text-gray-900 dark:text-white">10-Class Pass</strong> for $120 instead of charging per class. Here's how she uses subscriptions:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Maria creates an event called <strong class="text-gray-900 dark:text-white">"Class Passes"</strong> just to sell the pass on.</li>
            <li>On that event she adds a ticket, sets its <strong class="text-gray-900 dark:text-white">Type</strong> to "10-Class Pass" and its <strong class="text-gray-900 dark:text-white">Price</strong> to $120, then turns on <strong class="text-gray-900 dark:text-white">"This is a pass or subscription (multi-use)"</strong>.</li>
            <li>She picks the type <strong class="text-gray-900 dark:text-white">Visit pass (fixed number of visits)</strong>, sets <strong class="text-gray-900 dark:text-white">Number of visits</strong> to 10, sets <strong class="text-gray-900 dark:text-white">Valid for (days)</strong> to 90, leaves <strong class="text-gray-900 dark:text-white">Admissions per event</strong> at 1, and sets <strong class="text-gray-900 dark:text-white">Covered events</strong> to <strong class="text-gray-900 dark:text-white">All events in this schedule</strong>.</li>
            <li>A student buys the pass once and gets a QR code by email.</li>
            <li>At each class the front desk opens the scanner, sets <strong class="text-gray-900 dark:text-white">Scanning at event</strong> to that class, and scans the student's QR. The screen reads <strong class="text-green-700 dark:text-green-400">"Welcome - checked in"</strong> with <strong class="text-gray-900 dark:text-white">"1 of 10 visits used"</strong>, then "2 of 10 visits used", and so on. After ten classes it reads <strong class="text-red-700 dark:text-red-400">"All visits used"</strong>.</li>
            <li>Maria opens <strong class="text-gray-900 dark:text-white">Sales &rarr; Subscriptions</strong> any time to see who bought a pass and how many classes they've attended.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300">That's the whole feature in one story. The sections below explain each choice.</p>
    </section>

    <!-- Step 1: Create the pass -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Step 1 - Create the pass
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A subscription is a normal ticket type with one switch turned on. To create one:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit the event you want to sell the pass on and open the <strong class="text-gray-900 dark:text-white">Tickets</strong> section.</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Tickets</strong> (the third choice, after External and Registration), then stay on the <strong class="text-gray-900 dark:text-white">General</strong> tab.</li>
            <li>Add a ticket type and set its <strong class="text-gray-900 dark:text-white">Price</strong>. Leave <strong class="text-gray-900 dark:text-white">Quantity</strong> blank to sell an unlimited number of passes, or enter a number to cap the run. A ticket's name lives in the <strong class="text-gray-900 dark:text-white">Type</strong> field, which the form shows once the event carries more than one ticket type.</li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">"This is a pass or subscription (multi-use)"</strong>. It opens on <strong class="text-gray-900 dark:text-white">Season pass</strong> if the event repeats and <strong class="text-gray-900 dark:text-white">Visit pass</strong> if it does not.</li>
            <li>Pick the <strong class="text-gray-900 dark:text-white">Subscription type</strong> you want, then fill in the fields below it: <strong class="text-gray-900 dark:text-white">Admissions per event</strong>, <strong class="text-gray-900 dark:text-white">Number of visits</strong>, <strong class="text-gray-900 dark:text-white">Valid for (days)</strong> and <strong class="text-gray-900 dark:text-white">Covered events</strong>.</li>
            <li>Optionally turn on <strong class="text-gray-900 dark:text-white">"Let holders book seats in advance"</strong> and set its cancellation rules.</li>
            <li>Save the event.</li>
        </ol>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">A pass has one stock pool, not one per date</div>
            <p>On a normal ticket, <strong class="text-gray-900 dark:text-white">Quantity</strong> is the number available <em>on each date</em>. A pass is not tied to a date, so its quantity is a single pool across the whole run: set it to 50 and you sell 50 passes in total, however many events they cover.</p>
        </div>

        <h3 id="types" class="doc-subheading">Subscription types</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The type decides how many times the pass can be used:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>What it does</th>
                        <th>Use it for</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Visit pass</span><br><span class="text-gray-500 dark:text-gray-400">(fixed number of visits)</span></td>
                        <td>A set number of visits, which you enter in <strong class="text-gray-900 dark:text-white">Number of visits</strong>. Each visit is one event-day.</td>
                        <td>Class packs, punch cards, "10 entries" bundles</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Membership</span><br><span class="text-gray-500 dark:text-gray-400">(unlimited until expiry)</span></td>
                        <td>Unlimited visits to the covered events until the pass expires. Set the window in <strong class="text-gray-900 dark:text-white">Valid for (days)</strong>; leave it blank and the membership never expires.</td>
                        <td>Monthly or annual memberships</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Festival pass</span><br><span class="text-gray-500 dark:text-gray-400">(each event once)</span></td>
                        <td>One visit to each covered event. Once a covered event has been used, that event is spent even if other events remain.</td>
                        <td>Multi-day festivals, a conference series</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Season pass</span><br><span class="text-gray-500 dark:text-gray-400">(recurring event)</span></td>
                        <td>Every occurrence of the recurring event it is sold on, once per date. Only offered when the event repeats.</td>
                        <td>A weekly class or a recurring show</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Which fields you see depends on the type:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Number of visits</strong> appears for a Visit pass only, and is required.</li>
            <li><strong class="text-gray-900 dark:text-white">Valid for (days)</strong> counts from the moment of purchase, not from a fixed calendar date. Leave it blank for no expiry. Every type except a Season pass offers it.</li>
            <li><strong class="text-gray-900 dark:text-white">Covered events</strong> appears for every type except a Season pass, which always covers the dates of the recurring event it is sold on.</li>
            <li><strong class="text-gray-900 dark:text-white">Admissions per event</strong> and <strong class="text-gray-900 dark:text-white">Let holders book seats in advance</strong> are available on every type.</li>
        </ul>

        <h3 id="coverage" class="doc-subheading">Covered events</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Coverage decides <em>which</em> events the pass works at. It always resolves inside the schedule the selling event belongs to, so a pass can never be redeemed at another schedule's events.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Coverage</th>
                        <th>What it means</th>
                        <th>Good to know</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">All events in this schedule</span></td>
                        <td>The pass works at every event on the schedule.</td>
                        <td>Events you create later are covered automatically.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">All events in a sub-schedule</span></td>
                        <td>The pass works at every event in the sub-schedule you choose.</td>
                        <td>Future events in that sub-schedule are covered automatically. You need at least one sub-schedule to pick from.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Specific events</span></td>
                        <td>The pass works only at the events you hand-pick from the searchable list.</td>
                        <td>A fixed list - new events are not added automatically. At least one event is required.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">The "pass shop" pattern</div>
            <p>The tidiest way to sell a pass is to create one event just for it - for example "Memberships" or "Class Passes" - put the pass ticket there, and set its coverage to your real events.</p>
            <p class="mt-2">To keep that selling event off your public calendar, set <strong class="text-gray-900 dark:text-white">Visibility</strong> to <strong class="text-gray-900 dark:text-white">Unlisted</strong> in the event's <strong class="text-gray-900 dark:text-white">Details</strong> section: unlisted events are not listed on your schedule, but anyone with the link can still open the page and buy (you can add an optional password). <x-doc-badge plan="enterprise" /> Unlisted needs an Enterprise plan. Do not use <strong class="text-gray-900 dark:text-white">Draft</strong> or <strong class="text-gray-900 dark:text-white">Internal</strong> here: both are members-only, so guests could not reach the page to buy the pass at all.</p>
        </div>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">One pass per order</div>
            <p>A pass is a single redeemable unit: one QR code with one visit counter. Event Schedule enforces that for you by fixing <strong class="text-gray-900 dark:text-white">Max Per Order</strong> at 1 on any pass ticket, so the buyer cannot select more than one and you never need to set that field yourself. To buy passes as gifts, place a separate order for each.</p>
            <p class="mt-2">A pass also cannot share an order with normal single-date tickets. Trying it shows "A season pass cannot be purchased together with single-date tickets", so buy them in separate orders.</p>
        </div>
    </section>

    <!-- Step 2: Buyers purchase -->
    <section id="buying" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            Step 2 - Buyers purchase
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">For the guest, buying a pass is exactly like buying a normal ticket - they pick it, pay, and get a confirmation email.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Before they buy, the ticket carries a badge that says what it is:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Subscription &middot; 10 visits</strong> for a visit pass, <strong class="text-gray-900 dark:text-white">Subscription &middot; Unlimited visits</strong> for a membership, a plain <strong class="text-gray-900 dark:text-white">Subscription</strong> for a festival pass, or <strong class="text-gray-900 dark:text-white">Season Pass &middot; Valid for all dates</strong> for a season pass.</li>
            <li><strong class="text-gray-900 dark:text-white">"Book your dates after purchase"</strong> when advance booking is switched on, followed by the cancellation rule once you have set a deadline.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Their confirmation email carries the pass button ("Manage my season pass" - it opens every kind of pass). That private page shows:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Visits used</strong> - "2 / 10" on a visit pass, a running count on a festival pass, or "Unlimited visits" on a membership or season pass.</li>
            <li><strong class="text-gray-900 dark:text-white">Admissions per event</strong> - only when the pass admits more than one person.</li>
            <li><strong class="text-gray-900 dark:text-white">Valid until</strong> - the expiry date, if you set one.</li>
            <li><strong class="text-gray-900 dark:text-white">Covered events</strong> - the events the pass works at, so they know where to use it. Sub-schedule and specific-event passes list the events by name and date (up to 50); a pass covering the whole schedule shows "All events in this schedule" instead of a list, and a season pass lists nothing because it is simply every date of its own event.</li>
            <li>The QR code to show at the door, and the booking panel if advance booking is on.</li>
        </ul>
    </section>

    <section id="advance-booking" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Advance booking
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">By default a pass is scan-at-the-door: the holder just turns up and scans in. If you want holders to reserve a seat for specific dates ahead of time, turn on <strong class="text-gray-900 dark:text-white">"Let holders book seats in advance"</strong> on the pass.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">One shared pool of seats</strong> - advance bookings and regular ticket sales draw from the same capacity, so you can never oversell. If the room seats 50 and 30 holders book ahead, 20 seats remain for regular buyers.</li>
            <li><strong class="text-gray-900 dark:text-white">Max advance seats per date (optional)</strong> - caps how many seats holders may reserve on any one date, keeping some walk-up inventory aside. Leave it blank for no pass-specific limit.</li>
            <li><strong class="text-gray-900 dark:text-white">Holders book from their pass page</strong> - the private link in their confirmation email lists upcoming dates with the seats left on each; they book or cancel a date themselves, and each booking spends one visit up front, until they hit their limit.</li>
            <li><strong class="text-gray-900 dark:text-white">Booked or attended</strong> - the Subscriptions tab shows which dates a holder has reserved versus actually attended, and the check-in dashboard shows how many seats are reserved for the date.</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">On an event with <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated seating</a></div>
            <p>Booking ahead gives the holder an actual seat, chosen for them as the best available - they get no seat picker, and the seat is shown beside the date on their pass page. Cancelling gives that exact seat back.</p>
            <p>The pool is per <strong>price band</strong> rather than one room-wide number, and a pass may take a seat in any band: there is no way to restrict a pass to one part of the house.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The list of bookable dates covers up to a year ahead (at most 60 dates per covered event) and stops at the pass's expiry, so every date offered is a date the pass can still be redeemed on. On a festival pass, booking a date spends that event's single visit, so its other dates drop off the list.</p>

        <h3 id="cancellation-policy" class="doc-subheading">Cancellation policy</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Out of the box, holders can cancel a booked date at any time and the visit is credited back to their pass. With limited seats that invites no-shows: a late cancellation means the seat can't be resold and the waiting list never gets its chance. Two settings on the pass, both shown once advance booking is on, let you tighten this:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Cancellation deadline</strong> - how long before the event starts a booking can still be cancelled with the visit credited back. The choices are "Any time - visit always credited" (the default), "Until the event starts", or 12, 24, 48, 72 or 168 hours before the start.</li>
            <li><strong class="text-gray-900 dark:text-white">After the deadline</strong> - what a late cancellation does. This second setting only appears once you pick a deadline. <strong class="text-gray-900 dark:text-white">"Allow cancelling, but do not credit the visit back"</strong> (the default, also called forfeit) still releases the seat to other guests and the waiting list, but the visit stays spent, so a no-show doesn't get a free credit. <strong class="text-gray-900 dark:text-white">"Do not allow cancelling"</strong> closes cancellation entirely and the booking stands.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Holders always see the rules up front: the deadline appears on the ticket page before purchase, on their pass page next to each booked date, and in the booking confirmation email. A late "Cancel (no credit)" click asks for explicit confirmation before forfeiting the visit, and a mis-click is never fatal: any booking can be undone with full credit within 15 minutes of being made, even past the deadline. When a seat is freed before the event starts - credited or forfeited - anyone on the <a href="{{ route('marketing.docs.tickets') }}#waitlist" class="doc-link">waiting list</a> is notified automatically.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Forfeited but they turn up anyway?</div>
            <p>A forfeited booking never revives. If the holder shows up and scans in after forfeiting, that's a brand-new visit, subject to the pass's visit limits (on a festival pass, the one visit for that event is already spent). Changing the policy on the pass applies to existing bookings too.</p>
        </div>
    </section>

    <!-- Admissions per event -->
    <section id="admissions-per-event" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Admissions per event
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">By default a pass admits one person - the holder. To let the holder bring a guest (or a few), set <strong class="text-gray-900 dark:text-white">Admissions per event</strong> when you create the pass. It's the total number of people who can enter at each event, <strong class="text-gray-900 dark:text-white">including the holder</strong>: leave it at <strong class="text-gray-900 dark:text-white">1</strong> for holder-only, or set <strong class="text-gray-900 dark:text-white">2</strong> so they can bring one guest. When it's above 1, the holder's pass page shows the number too.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">A guest doesn't use up a visit</div>
            <p>Party size is separate from the visit count. A Visit pass good for 10 visits with 2 admissions per event still counts each event as a <strong class="text-gray-900 dark:text-white">single</strong> visit - so the holder can bring a guest to all 10 events.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">At the door it's one scan per person:</p>
        <ul class="doc-list mb-6">
            <li>Scan the QR once for each person, up to the limit. The scanner shows <strong class="text-gray-900 dark:text-white">"Admitted 1 of 2"</strong> with <strong class="text-gray-900 dark:text-white">"Scan again to admit the guest."</strong>, then <strong class="text-gray-900 dark:text-white">"Admitted 2 of 2"</strong> and <strong class="text-gray-900 dark:text-white">"All guests admitted."</strong></li>
            <li>If the group arrives together, tap the <strong class="text-gray-900 dark:text-white">"Admit guest"</strong> button (it shows how many admissions are left) instead of pointing the camera at the same QR again.</li>
            <li>Once every admission is used, a further scan reads <strong class="text-gray-900 dark:text-white">"All 2 admissions already used today"</strong> - and, as always, no extra visit is spent.</li>
            <li>Extra people count against the event's capacity, so an extra admission is only granted while the date still has a free seat.</li>
            <li>The <a href="{{ route('marketing.docs.tickets') }}#checkin-dashboard" class="doc-link">check-in dashboard</a> shows a second headcount, "admitted (incl. guests)", next to the number of passes checked in.</li>
        </ul>
        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Example: two membership tiers</div>
            <p>Sell an <strong class="text-gray-900 dark:text-white">"Apprentice"</strong> pass with Admissions per event set to <strong class="text-gray-900 dark:text-white">1</strong> - the holder comes alone - and an <strong class="text-gray-900 dark:text-white">"Explorer"</strong> pass set to <strong class="text-gray-900 dark:text-white">2</strong> that lets them bring a friend to every event, all without using extra visits.</p>
        </div>
    </section>

    <!-- Step 3: Scan at the door -->
    <section id="redeeming" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
            </svg>
            Step 3 - Scan at the door
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Because a pass can be valid across many events, the scanner needs to know <em>which</em> event you're checking people into right now.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> on your phone and tap <strong class="text-gray-900 dark:text-white">Scan Ticket</strong>.</li>
            <li>At the top, check <strong class="text-gray-900 dark:text-white">Scanning at event</strong> and set it to the event happening now. It arrives pre-selected (an event with sales today, otherwise one running today, otherwise your most recent), it remembers your last choice on that device, and it shows the choice back to you as "Scanning at: &hellip;". The list holds your 100 most recent events, whether or not they sell tickets of their own.</li>
            <li>Point the camera at the guest's QR code. A visit is recorded and the result appears.</li>
        </ol>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">One visit per event, per day</div>
            <p>Scanning the same person at the same event again on the same day shows "Already checked in today" and does <strong class="text-gray-900 dark:text-white">not</strong> use another visit - so there's no harm in double-scanning. (On a pass that admits more than one person, the repeat scan admits the next person on that same visit instead.)</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Check-in for a date opens <strong class="text-gray-900 dark:text-white">24 hours before</strong> the event starts and closes when it ends (its start plus its duration, or two hours if no duration is set). Outside that window the pass still reads as valid, and the scanner tells you when the doors open or that the event is over.</p>

        <h3 id="scan-results" class="doc-subheading">What the scanner shows</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Result</th>
                        <th>What it means</th>
                        <th>What to do</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-green-700 dark:text-green-400">Welcome - checked in</span></td>
                        <td>A visit was recorded, with the running count below it: "3 of 10 visits used" on a visit pass, "Unlimited visits" on a membership or season pass, "One visit per event" on a festival pass.</td>
                        <td>Let them in.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-green-700 dark:text-green-400">Admitted 1 of 2</span></td>
                        <td>A pass that admits more than one person let someone in and still has an admission left for this event.</td>
                        <td>Scan the next person, or tap <strong class="text-gray-900 dark:text-white">Admit guest</strong>. No extra visit is used.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-amber-700 dark:text-amber-400">Already checked in today</span></td>
                        <td>They already entered this event today. The time of that entry is shown.</td>
                        <td>Let them in - no extra visit is used.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-amber-700 dark:text-amber-400">All 2 admissions already used today</span></td>
                        <td>Every admission on a multi-person pass has been used at this event today.</td>
                        <td>Sell a ticket for anyone else in the group.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-700 dark:text-gray-200">Valid pass</span></td>
                        <td>The pass is fine but it's too early: check-in for this date hasn't opened yet. The line below gives the opening time.</td>
                        <td>Ask them to come back, then scan again.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-700 dark:text-gray-200">Valid pass - today's event has ended</span></td>
                        <td>The occurrence you're scanning at has finished.</td>
                        <td>Nothing to do - no visit is used.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-700 dark:text-gray-200">Valid pass - no event scheduled today</span></td>
                        <td>The selected event doesn't run today. The next date is shown.</td>
                        <td>Check the <strong class="text-gray-900 dark:text-white">Scanning at event</strong> selector is set to the event in front of you.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">All visits used</span></td>
                        <td>The pass has reached its visit limit, or a festival pass has already been used at this event.</td>
                        <td>Sell a new pass or a single ticket.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">This pass has expired</span></td>
                        <td>The pass is past its valid-until date.</td>
                        <td>Sell a new pass.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">Not valid for this event</span></td>
                        <td>This pass doesn't cover the event you're scanning at.</td>
                        <td>Check the <strong class="text-gray-900 dark:text-white">Scanning at event</strong> selector; otherwise sell a ticket.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-red-700 dark:text-red-400">Not paid, cancelled or refunded</span></td>
                        <td>The order behind the pass isn't a completed sale any more.</td>
                        <td>Settle the payment or sell a ticket. A payment problem is the only thing the scanner treats as a hard error - every other outcome above is a neutral status, so a good pass never reads as fraud.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Step 4: Track usage -->
    <section id="monitoring" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Step 4 - Track usage
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose the <strong class="text-gray-900 dark:text-white">Subscriptions</strong> tab, which carries the number of passes sold. The page covers every schedule you own, and a summary line at the top counts the passes and the visits redeemed across them.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Each paid pass is one row, sorted by holder name:</p>
        <ul class="doc-list mb-6">
            <li>Their name and email, the pass type, the visit count, and the expiry date.</li>
            <li>The visit count reads <strong class="text-gray-900 dark:text-white">2 / 10</strong> on a visit pass, which has a limit to count against, and as a plain total ("3 visits") on every other type.</li>
            <li>A status of <strong class="text-gray-900 dark:text-white">Active</strong>, <strong class="text-gray-900 dark:text-white">Used up</strong> (a visit pass at its limit) or <strong class="text-gray-900 dark:text-white">Expired</strong>.</li>
            <li>Expand the row for the visit log: which event, the date and time, and whether the visit is <strong class="text-gray-900 dark:text-white">Attended</strong>, <strong class="text-gray-900 dark:text-white">Booked</strong> (reserved in advance, not yet scanned) or <strong class="text-gray-900 dark:text-white">Forfeited</strong> (a late cancellation that kept the visit spent).</li>
            <li>A <strong class="text-gray-900 dark:text-white">View Ticket</strong> link opens the holder's own pass page, which is handy for support questions.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Paid passes only</div>
            <p>The tab lists passes whose order is a completed sale. An unpaid, cancelled or refunded order is not shown, so a pass you refund disappears from the list along with its visit log. If you need that history, export or note it before issuing the refund.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300">The real-time <a href="{{ route('marketing.docs.tickets') }}#checkin-dashboard" class="doc-link">check-in dashboard</a> also counts pass scans alongside regular tickets, and shows how many seats holders have reserved in advance.</p>
    </section>

    <!-- Good to know -->
    <section id="good-to-know" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Good to know
        </h2>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">One visit per event, per day.</strong> Re-scanning the same person at the same event the same day never uses a second visit.</li>
            <li><strong class="text-gray-900 dark:text-white">Bring a guest.</strong> A pass can admit more than one person per event (set <strong class="text-gray-900 dark:text-white">Admissions per event</strong>) without using extra visits.</li>
            <li><strong class="text-gray-900 dark:text-white">Sold on its own.</strong> One order buys one pass, and a pass can't share an order with normal single-date tickets.</li>
            <li><strong class="text-gray-900 dark:text-white">Not auto-renewing.</strong> It's a one-time purchase; there's no recurring billing, and no card is kept on file for the holder.</li>
            <li><strong class="text-gray-900 dark:text-white">Refunds.</strong> Cancelling or refunding the order stops the pass from being scanned (the scanner reports it as refunded), releases any seats it had reserved, and removes the pass from the Subscriptions tab together with its visit log.</li>
            <li><strong class="text-gray-900 dark:text-white">Future events.</strong> "All events" and "sub-schedule" coverage automatically include events you create later; "Specific events" does not.</li>
            <li><strong class="text-gray-900 dark:text-white">Advance booking is checked when it is used, not at the till.</strong> Booking dates ahead needs the schedule to be on a paid plan at that moment, so a pass sold while you were on Pro stops taking bookings if the schedule has since lapsed to free. Scanning it in at the door carries on working.</li>
            <li><strong class="text-gray-900 dark:text-white">Webhooks.</strong> <x-doc-badge plan="pro" /> Pass scans and advance bookings fire <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">webhooks</a> (<strong class="text-gray-900 dark:text-white">ticket.scanned</strong>, <strong class="text-gray-900 dark:text-white">ticket.booked</strong> and <strong class="text-gray-900 dark:text-white">ticket.booking_cancelled</strong>) if you want to feed another system.</li>
            <li><strong class="text-gray-900 dark:text-white">Plan.</strong> <x-doc-badge plan="pro" /> Selling tickets and scanning them at the door are on every plan (25 paid tickets a month on the free plan); the pass itself is the part that needs Pro, which also lifts the monthly cap. Selfhosted installs include the lot.</li>
        </ul>
    </section>

    <!-- See also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - set up ticketing, payment, and ticket types</li>
            <li><a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">Check-in at the Door</a> - scanning QR codes and the check-in dashboard</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - add the events your pass covers</li>
            <li><a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift Cards</a> - sell a prepaid balance spendable on any event</li>
            <li><a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointments</a> - sell bookable time slots instead of event admission</li>
            <li><a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">Plan</a> - your own Pro or Enterprise subscription and billing</li>
        </ul>
    </section>
</x-docs-page>
