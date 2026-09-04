<x-docs-page
    key="appointments"
    description="Set up Calendly-style appointment booking: create appointment types with weekly hours and buffers, and take free or paid bookings."
    lede="Let guests book time with you on a public page, Calendly-style. You set the hours you are open, they pick an open slot, and everyone gets a confirmation."
    article-description="How to offer appointment booking: create appointment types with weekly hours, buffers, and optional payment, and let guests book a time on your public page."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">How booking works</x-doc-nav-link>
        <x-doc-nav-link href="#appointment-types">Appointment types</x-doc-nav-link>
        <x-doc-nav-link href="#weekly-hours">Weekly hours</x-doc-nav-link>
        <x-doc-nav-link href="#date-overrides">Date overrides</x-doc-nav-link>
        <x-doc-nav-link href="#buffers-and-notice">Scheduling rules</x-doc-nav-link>
        <x-doc-nav-link href="#location">Where you meet</x-doc-nav-link>
        <x-doc-nav-link href="#guest-details">What you ask guests</x-doc-nav-link>
        <x-doc-nav-link href="#payments">Payments</x-doc-nav-link>
        <x-doc-nav-link href="#approval">Approval</x-doc-nav-link>
        <x-doc-nav-link href="#bookings">Managing bookings</x-doc-nav-link>
        <x-doc-nav-link href="#rescheduling">Rescheduling</x-doc-nav-link>
        <x-doc-nav-link href="#guest-booking">The booking page</x-doc-nav-link>
        <x-doc-nav-link href="#good-to-know">Good to know</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            How booking works
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Appointment booking lets guests reserve a time with you on a public page. You define <strong class="text-gray-900 dark:text-white">appointment types</strong> (for example a 30 minute intro call), and guests pick an open slot and book it.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">You</strong> create one or more appointment types and set the hours you take bookings.</li>
            <li><strong class="text-gray-900 dark:text-white">A guest</strong> opens your booking page, picks a day and time, and enters their details.</li>
            <li><strong class="text-gray-900 dark:text-white">Both of you</strong> are emailed. The guest gets a calendar invite, a link to manage the booking, and a reminder before it starts.</li>
        </ol>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Bookings stay off your public schedule</div>
            <p>Every booking is created as an unlisted event, so it never shows up on your public schedule, your iCal feed, your RSS feed, or your event graphics. It does block the time against further bookings, it syncs to your own connected calendars, and it appears on your Sales page.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Appointment booking is available on <strong class="text-gray-900 dark:text-white">every plan</strong>. What the plan controls is how many appointment types you can offer at once, not what a type can do: the single Free type has weekly hours, date overrides, buffers, approval and payment just like any other.</p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Appointment types</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Free</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Pro</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Enterprise</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Selfhosted</td>
                        <td>Unlimited</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300">If a Pro plan lapses, nothing is deleted. Every type you built is kept, the oldest bookable one stays bookable, and the rest light up again when you upgrade. The Appointments tab names the type that is currently live so a booking link is never mysteriously missing.</p>
    </section>

    <!-- Appointment types -->
    <section id="appointment-types" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            Appointment types
        </h2>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your schedule in the admin panel and go to the <strong class="text-gray-900 dark:text-white">Appointments</strong> tab.</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">New appointment type</strong>.</li>
            <li>Fill in the <strong class="text-gray-900 dark:text-white">Details</strong> at the top, then work down the editor: Availability, Scheduling rules, Location, Price, Booking form.</li>
            <li>Leave <strong class="text-gray-900 dark:text-white">Active</strong> on at the bottom and choose <strong class="text-gray-900 dark:text-white">Save</strong>.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The rest of this page follows those editor sections in order. The <strong class="text-gray-900 dark:text-white">Details</strong> section itself holds three fields:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Name</strong> - what guests see in the list, for example "30 minute intro call". A new type starts out named "30 minute meeting".</li>
            <li><strong class="text-gray-900 dark:text-white">Description</strong> - optional detail shown under the name on the booking page.</li>
            <li><strong class="text-gray-900 dark:text-white">Duration</strong> - how long the appointment runs. Tap one of the presets (15, 30, 45, 60, 90 or 120 minutes) or type any value from 5 minutes to 24 hours in the box beside them.</li>
        </ul>
        <h3 class="doc-subheading">The types list</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Back on the Appointments tab, each type shows its duration, its price, whether it requires confirmation, and how many bookings it has taken. The buttons on the row are:</p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Active</td>
                        <td>A switch that turns the type on or off for guests. Turning it off stops new bookings and hides the type from your booking page. Bookings already made are unaffected.</td>
                    </tr>
                    <tr>
                        <td>Copy link</td>
                        <td>Copies that type's own booking address, so you can link straight to it. Only shown while the type is bookable.</td>
                    </tr>
                    <tr>
                        <td>Edit</td>
                        <td>Reopens the type in the editor.</td>
                    </tr>
                    <tr>
                        <td>Clone</td>
                        <td>Makes a copy named "... (Copy)" and opens it in the editor, switched off, so you rename it before anyone can book it. A clone uses one of your plan's appointment types.</td>
                    </tr>
                    <tr>
                        <td>Delete</td>
                        <td>Stops guests booking the type. Bookings already made are kept, and so is their history on the Sales page.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Create as many types as your plan allows. A free intro call and a paid consultation can sit side by side with different hours, prices, and rules.</p>
    </section>

    <!-- Weekly hours -->
    <section id="weekly-hours" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Weekly hours
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Weekly hours</strong> sits at the top of the editor's <strong class="text-gray-900 dark:text-white">Availability</strong> section. Tick the days you take bookings and set the hours for each one. A new type starts on Monday to Friday, 09:00 to 17:00.</p>
        <ul class="doc-list mb-6">
            <li>A day can hold up to <strong class="text-gray-900 dark:text-white">four ranges</strong>, so mornings and late afternoons can be open while the middle of the day is not.</li>
            <li>Ranges cannot overlap, and each one has to end after it starts. Start and end times are offered in 15 minute steps, labelled in your schedule's 12 or 24 hour format.</li>
            <li><strong class="text-gray-900 dark:text-white">Copy to all selected days</strong> pushes one day's ranges onto every other ticked day, which is much faster than setting a week a dropdown at a time.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Below the hours, <strong class="text-gray-900 dark:text-white">Start times every</strong> controls how often a slot is offered inside those hours: 5, 10, 15, 20, 30 or 60 minutes. Leave it on <strong class="text-gray-900 dark:text-white">Same as the duration</strong> and slots line up back to back, so a 30 minute call is offered at 9:00, 9:30, 10:00. Set it to 15 instead and the same call can also start at 9:15 or 9:45, which fills a day more densely at the cost of leaving odd gaps behind.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Hours are in your <strong class="text-gray-900 dark:text-white">schedule's timezone</strong>. Guests always see open times converted to their own timezone, with your timezone shown alongside so nobody has to do the maths.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Set your timezone first</div>
            <p>If your schedule has no timezone set, slots are worked out in the application's default timezone and the Appointments tab warns you. Set it under <a href="{{ route('marketing.docs.creating_schedules') }}#details-localization" class="doc-link">Edit Schedule, Details</a> before you share your booking page.</p>
        </div>
        <h3 class="doc-subheading">What else blocks a slot</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open times also respect what is already on your schedule. Anything that overlaps a slot removes it:</p>
        <ul class="doc-list mb-6">
            <li>Ordinary events on your schedule, including recurring ones, and events synced in from <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Google Calendar, Outlook or CalDAV</a>.</li>
            <li>Bookings for your other appointment types, plus their buffers.</li>
            <li>Requests still waiting on your approval. A pending request holds its time until you accept or decline it.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">An event with no duration set blocks two hours, which errs on the side of not double-booking you. An all-day event with no start time does not block any slot, so use a timed event when you want a slot closed.</p>
        <p class="text-gray-600 dark:text-gray-300">If a type has no weekly hours at all, the Appointments tab flags it: guests would reach the booking page and find no times to pick.</p>
    </section>

    <!-- Date overrides -->
    <section id="date-overrides" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Date overrides
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Weekly hours repeat every week, so use a date override when one particular day is different. <strong class="text-gray-900 dark:text-white">Date overrides</strong> is the last block in the editor's Availability section.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Choose <strong class="text-gray-900 dark:text-white">Add date</strong> and pick the date.</li>
            <li>Leave it marked <strong class="text-gray-900 dark:text-white">Unavailable</strong> to close the date completely, or untick that and set the hours you are open just for that date.</li>
            <li>Save the type. Removing the row later puts that date back on its normal weekly hours.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">An override replaces the weekly hours for that date rather than adding to them, and the same four-range, no-overlap rule applies. Public holidays, a day off, a morning-only Friday before a long weekend, and a one-off late evening are all overrides.</p>
        <div class="doc-callout mb-6">
            <div class="doc-callout-title">Overrides are per appointment type</div>
            <p>Each type keeps its own overrides, so closing a date for one type does not close it for the others. If you take a whole week off, add the override to every type you have active, or turn the types off instead.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Past overrides stay listed so you can tidy them up, and they have no effect on open times.</p>
    </section>

    <!-- Buffers and notice -->
    <section id="buffers-and-notice" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Scheduling rules
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The editor's <strong class="text-gray-900 dark:text-white">Scheduling rules</strong> section decides how close to an appointment, and how far ahead, a guest may book.</p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What it does</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Buffer before (minutes)</td>
                        <td>Keeps the time immediately before each appointment free, so bookings never touch back to back.</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>Buffer after (minutes)</td>
                        <td>The same padding after each appointment. Both buffers block the neighbouring slots on every type, not just this one.</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>Minimum notice (hours)</td>
                        <td>How far ahead a guest has to book. Set 12, for example, and nobody can grab a slot this evening. The booking page tells guests the rule up front.</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>Booking window (days)</td>
                        <td>How many days into the future guests can book, up to a maximum of 730.</td>
                        <td>60</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Minimum notice and the booking window are limits on <em>guests</em>. When you move a booking yourself, your own picker ignores both. See <a href="#rescheduling" class="doc-link">Rescheduling</a>.</p>
    </section>

    <!-- Location -->
    <section id="location" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            Where you meet
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Location</strong> section says how the appointment happens, and only the matching field is shown:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">In person</strong> - enter the address guests should come to.</li>
            <li><strong class="text-gray-900 dark:text-white">Online</strong> - enter the meeting link. It is attached to the booking as the event link, so the guest gets it with their confirmation. The booking page tells them the joining link arrives after they book.</li>
            <li><strong class="text-gray-900 dark:text-white">Phone</strong> - enter the number to call. Leave it blank and the booking shows the guest's own number instead, which is the right way round when you are the one calling them.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Whichever you choose travels with the booking: it shows on the guest's booking page, in the confirmation email, and in the calendar invite.</p>
    </section>

    <!-- Guest details -->
    <section id="guest-details" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            What you ask guests
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every booking collects a <strong class="text-gray-900 dark:text-white">name</strong> and <strong class="text-gray-900 dark:text-white">email address</strong>, which is where the confirmation goes. The editor's <strong class="text-gray-900 dark:text-white">Booking form</strong> section adds two options on top of that:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Ask for a phone number</strong> - adds an optional phone field.</li>
            <li><strong class="text-gray-900 dark:text-white">Require a phone number</strong> - makes that field mandatory. It only appears once you are asking for a number at all, and only counts while you are.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Guests can also leave <strong class="text-gray-900 dark:text-white">notes</strong> with anything that helps you prepare. Notes are saved on the booking and shown on its row in the Bookings list, so they are there when you need them. If you want structured questions on your ticket checkout instead, see <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Custom Fields</a>.</p>
    </section>

    <!-- Payments -->
    <section id="payments" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Payments
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Leave the <strong class="text-gray-900 dark:text-white">Price</strong> at zero for a free type. Enter an amount and the currency and payment method appear: paid types take payment by <strong class="text-gray-900 dark:text-white">cash</strong>, <strong class="text-gray-900 dark:text-white">Stripe</strong>, or a <strong class="text-gray-900 dark:text-white">payment link</strong>. A currency is required once there is a price.</p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>What the guest does</th>
                        <th>How long the slot is held</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Free</td>
                        <td>Books and is confirmed straight away.</td>
                        <td>Until the appointment, or until it is cancelled</td>
                    </tr>
                    <tr>
                        <td>Cash</td>
                        <td>Books and is confirmed straight away, with the balance due when you meet. You mark the sale paid on the Sales page.</td>
                        <td>Until the appointment, or until it is cancelled</td>
                    </tr>
                    <tr>
                        <td>Stripe</td>
                        <td>Goes to Stripe Checkout. The booking confirms when the payment clears.</td>
                        <td>1 hour, then the slot is released</td>
                    </tr>
                    <tr>
                        <td>Payment link</td>
                        <td>Leaves for your own payment page, and gets an email with a "complete your payment" note and their manage link. You mark the sale paid on the Sales page, which sends the confirmation.</td>
                        <td>24 hours, then the slot is released and the guest is told</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Stripe payments are created directly on your own connected Stripe account with no platform fee, exactly as ticket sales are.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Paid types need a payment method</div>
            <p>A paid type stays hidden from guests until a working payment method is connected, and the Appointments tab marks the type that is being held back. Connect Stripe or add a payment link under <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Account Settings</a> to make the type bookable.</p>
        </div>
        <div class="doc-callout doc-callout-warning mb-2">
            <div class="doc-callout-title">Refunds are manual</div>
            <p>Cancelling a paid booking does not move any money. Refund it in Stripe or your payment provider, then mark the sale refunded on the Sales page. The cancellation email to you shows the amount and reference so you have both to hand.</p>
        </div>
    </section>

    <!-- Approval -->
    <section id="approval" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Approval
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">Require approval before confirming</strong> in the Booking form section and bookings for that type arrive as requests. The type is labelled "Requires confirmation" on the booking page, and the guest is told nothing is booked until you confirm.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Requests appear in two places, and the Appointments tab carries a count of the bookings still waiting on you:</p>
        <ul class="doc-list mb-6">
            <li>The <strong class="text-gray-900 dark:text-white">Requests</strong> tab, alongside events submitted to your schedule. Each request is a card showing the guest, the time, the amount and their notes, with <strong class="text-gray-900 dark:text-white">Accept</strong> and <strong class="text-gray-900 dark:text-white">Decline</strong> at the bottom. <strong class="text-gray-900 dark:text-white">Accept all</strong> at the top of that tab clears the whole list at once, bookings included, so read it before using it.</li>
            <li>The <strong class="text-gray-900 dark:text-white">Pending</strong> filter of your Bookings list, which adds a third answer: <strong class="text-gray-900 dark:text-white">Change time</strong>, to propose a different slot instead of saying yes or no. See <a href="#rescheduling" class="doc-link">Rescheduling</a>.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Accepting sends the guest their confirmation and calendar invite and syncs the booking to your connected calendars. Declining tells them they are welcome to book another time and releases the slot.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The slot stays blocked while a request is open, so you cannot double-book yourself while you decide. A request whose time has already passed can no longer be approved.</p>
        <p class="text-gray-600 dark:text-gray-300">On a paid type that takes Stripe or payment link payments, accepting does not confirm on its own: the guest is confirmed once the payment goes through.</p>
    </section>

    <!-- Managing bookings -->
    <section id="bookings" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Managing bookings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Switch the Appointments tab from <strong class="text-gray-900 dark:text-white">Appointment types</strong> to <strong class="text-gray-900 dark:text-white">Bookings</strong> to see what has been booked, filtered by <strong class="text-gray-900 dark:text-white">Upcoming</strong>, <strong class="text-gray-900 dark:text-white">Pending</strong>, <strong class="text-gray-900 dark:text-white">Past</strong>, or <strong class="text-gray-900 dark:text-white">Cancelled</strong>. Anything waiting on your approval lives under Pending only, so it never gets buried among the settled bookings in Upcoming. There is also a search box for a guest's name or email address.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each row already carries the whole booking: the date and time, the appointment type, the guest's name, email and phone number, their notes, the amount, and a status.</p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>What it means</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Confirmed</td>
                        <td>Paid (or free) and locked in. The guest has their calendar invite.</td>
                    </tr>
                    <tr>
                        <td>Request sent</td>
                        <td>Waiting on your approval. The slot is held in the meantime.</td>
                    </tr>
                    <tr>
                        <td>Complete your payment</td>
                        <td>Booked but not yet paid: a cash booking with the balance due, or a card or link hold still waiting to clear.</td>
                    </tr>
                    <tr>
                        <td>Cancelled</td>
                        <td>Cancelled, refunded, or an unpaid hold that expired. The slot has been released.</td>
                    </tr>
                    <tr>
                        <td>Moved</td>
                        <td>An extra tag on any booking that has been rescheduled at least once.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A row still waiting on you leads with <strong class="text-gray-900 dark:text-white">Decline</strong> and <strong class="text-gray-900 dark:text-white">Accept</strong>, so it asks one clear question. Once a booking has been decided on, the row offers instead:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Preview</strong> opens the guest's own booking page in a new tab, exactly as they see it.</li>
            <li><strong class="text-gray-900 dark:text-white">Reschedule</strong> moves a booking to another time without cancelling it. See <a href="#rescheduling" class="doc-link">Rescheduling</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel appointment</strong> emails the guest and releases the slot. It is not offered on a booking that has already happened.</li>
            <li><strong class="text-gray-900 dark:text-white">New bookings email you too</strong>, whether they are confirmed or waiting for your approval. The notice goes to every team member whose <a href="{{ route('marketing.docs.creating_schedules') }}#settings-notifications" class="doc-link">notification settings</a> ask for it.</li>
            <li><strong class="text-gray-900 dark:text-white">Paid bookings appear on the Sales page</strong>, where you mark them paid or refunded and see the revenue with the rest of your sales.</li>
        </ul>
    </section>

    <!-- Rescheduling -->
    <section id="rescheduling" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Rescheduling
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A booking can be moved to another time instead of being cancelled and booked again. The booking keeps its payment, its private link, and its place on your Sales page, so nothing has to be refunded and re-charged to change a time.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Either side can start it:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">The guest</strong> uses <strong class="text-gray-900 dark:text-white">Reschedule</strong> on their private link, picks from the same times anyone else would see, and can do it right up until the appointment starts.</li>
            <li><strong class="text-gray-900 dark:text-white">You</strong> use <strong class="text-gray-900 dark:text-white">Reschedule</strong> on the Bookings row. Your picker ignores the type's minimum notice and booking window, so you can move something to later today or up to a year out. Times that have already passed are not offered, and weekly hours, date overrides, buffers and times you are already booked all still apply, so you cannot double-book yourself.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Whoever did not start the move is emailed about it. The guest's email carries an updated calendar invite that moves the entry already in their calendar rather than adding a second one, and your own connected calendars are updated too. When you move a booking you can add a note for the guest, or choose not to email them at all.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Any move tags the Bookings row <strong class="text-gray-900 dark:text-white">Moved</strong>, so it is still obvious later. If a guest moves a booking less than a day before its original time, the email to you says so as well.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">On a type that requires approval</div>
            <p>A guest moving their own booking sends it back to your Requests tab, because you approved one time and not any time. Their old slot is released the moment they pick a new one, and the guest is told that before they commit. A move you make yourself does not need your own approval and stays confirmed.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A booking cannot be moved when:</p>
        <ul class="doc-list mb-6">
            <li>It has already started, or has been cancelled, refunded or expired.</li>
            <li>It is a Stripe or payment link booking still waiting on payment. Those holds expire on their own clock, so the guest pays first and can then move it freely.</li>
            <li>Its appointment type has been turned off or deleted, since a move commits a brand new slot.</li>
            <li>It was moved within the last three minutes. That pause is the only limit: a booking can be moved as soon as it is made, and there is no cap on how many times.</li>
        </ul>
    </section>

    <!-- The booking page -->
    <section id="guest-booking" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            The booking page
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once a type is bookable, the Appointments tab shows <strong class="text-gray-900 dark:text-white">Your booking page</strong> with the link to copy, share and preview. It lives at <code class="doc-inline-code">/book</code> on your schedule, and each type has its own address underneath it, so you can link straight to one type. When only one type is bookable, <code class="doc-inline-code">/book</code> takes guests straight to it instead of showing a list of one.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Book a Time</strong> button also appears on your public schedule page. Its wording is editable under <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-labels" class="doc-link">Customize, Custom Labels</a> <x-doc-badge plan="pro" />, like your other labels.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Guests see each type with its duration and price, then move through two steps: pick a date and a time, then enter their details. Times are grouped into morning, afternoon, and evening, with a shortcut to the next available slot. Everything is shown in the guest's own timezone, detected from their browser and changeable from a picker, with your schedule's timezone alongside it. After they confirm they get:</p>
        <ul class="doc-list mb-6">
            <li>A confirmation email with an <code class="doc-inline-code">.ics</code> calendar invite attached.</li>
            <li>A private link to manage the booking. It offers an <strong class="text-gray-900 dark:text-white">Add to Calendar</strong> menu for Google Calendar, Outlook and a calendar file, and lets them <a href="#rescheduling" class="doc-link">move the booking to another time</a> or cancel it while the appointment is still in the future.</li>
            <li>A reminder email about 24 hours before it starts, with the invite attached again.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">If nothing on your schedule is bookable, the booking page is simply not there, and the Book a Time button does not appear.</p>
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
            <li><strong class="text-gray-900 dark:text-white">Email has to work.</strong> On the hosted platform, guests get no confirmations or reminders until the schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a> are configured, and the Appointments tab warns you about it.</li>
            <li><strong class="text-gray-900 dark:text-white">Reminders only go to confirmed bookings.</strong> A request still waiting on you, or a card booking still waiting on payment, does not get one.</li>
            <li><strong class="text-gray-900 dark:text-white">The same guest cannot double-book.</strong> One email address cannot hold two bookings that start at the same time on your schedule.</li>
            <li><strong class="text-gray-900 dark:text-white">Bookings do not use your ticket allowance.</strong> On the Free plan, appointment bookings never count towards the monthly paid-ticket cap, however many you take.</li>
            <li><strong class="text-gray-900 dark:text-white">Turn a type off rather than delete it.</strong> Switching it off hides it from guests and keeps everything already booked, and you can switch it back on later.</li>
            <li><strong class="text-gray-900 dark:text-white">Not the same as Availability.</strong> <a href="{{ route('marketing.docs.availability') }}" class="doc-link">Availability</a> <x-doc-badge plan="enterprise" /> is a tab on talent schedules that marks whole days your team members are unavailable to be booked for events. Appointments offer specific time slots on any schedule type, on any plan.</li>
            <li><strong class="text-gray-900 dark:text-white">Plan.</strong> Appointment booking is on every plan. Free covers one appointment type; Pro and Enterprise are unlimited, as is every selfhosted deployment.</li>
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
            <li><a href="{{ route('marketing.docs.managing_schedules') }}#appointments" class="doc-link">Managing Schedules</a> - the Appointments tab and the Requests tab in context</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#details-localization" class="doc-link">Creating Schedules</a> - set your timezone, email settings, and calendar sync</li>
            <li><a href="{{ route('marketing.docs.availability') }}" class="doc-link">Availability</a> - mark whole days your team members are unavailable</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - the Sales page, where paid bookings and refunds live</li>
            <li><a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Account Settings</a> - connect Stripe so paid types can take payment</li>
        </ul>
    </section>
</x-docs-page>
