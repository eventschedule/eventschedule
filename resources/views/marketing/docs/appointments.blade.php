<x-docs-page
    key="appointments"
    description="Set up Calendly-style appointment booking: create appointment types with weekly hours and buffers, take free or paid bookings, require approval, and manage bookings."
    lede="Let guests book time with you on a public page, Calendly-style. You set the hours you are open, they pick an open slot, and everyone gets a confirmation."
    article-description="How to offer appointment booking: create appointment types with weekly hours, buffers, and optional payment, and let guests book a time on your public page."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">How booking works</x-doc-nav-link>
        <x-doc-nav-link href="#appointment-types">Appointment types</x-doc-nav-link>
        <x-doc-nav-link href="#weekly-hours">Weekly hours</x-doc-nav-link>
        <x-doc-nav-link href="#date-overrides">Date overrides</x-doc-nav-link>
        <x-doc-nav-link href="#buffers-and-notice">Buffers, notice &amp; window</x-doc-nav-link>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            How booking works
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Appointment booking lets guests reserve a time with you on a public page. You define <strong class="text-gray-900 dark:text-white">appointment types</strong> (for example a 30 minute intro call), and guests pick an open slot and book it.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">You</strong> create one or more appointment types and set the hours you take bookings.</li>
            <li><strong class="text-gray-900 dark:text-white">A guest</strong> opens your booking page, picks a day and time, and enters their details.</li>
            <li><strong class="text-gray-900 dark:text-white">Both of you</strong> are emailed. The guest gets a calendar invite, a link to manage the booking, and a reminder before it starts.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Bookings stay off your public schedule</div>
            <p>Every booking is created as a private event, so it never shows up on your public schedule, your iCal feed, your RSS feed, or your event graphics. It does block the time against further bookings, and it appears on your Sales page.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Appointment booking is a <strong class="text-gray-900 dark:text-white">Pro</strong> feature, and is included on all selfhosted deployments.</p>
    </section>

    <!-- Appointment types -->
    <section id="appointment-types" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            Appointment types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open the <strong class="text-gray-900 dark:text-white">Appointments</strong> tab on your schedule and choose <strong class="text-gray-900 dark:text-white">New appointment type</strong>. Each type has:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Name</strong> - what guests see in the list, for example "30 minute intro call".</li>
            <li><strong class="text-gray-900 dark:text-white">Description</strong> - optional detail shown under the name on the booking page.</li>
            <li><strong class="text-gray-900 dark:text-white">Duration</strong> - how long the appointment runs, in minutes.</li>
            <li><strong class="text-gray-900 dark:text-white">Start times every</strong> - how often a slot is offered, from 5 minutes up to an hour. Leave it on <strong class="text-gray-900 dark:text-white">Same as the duration</strong> and slots line up back to back, so a 30 minute call is offered at 9:00, 9:30, 10:00. Set it to 15 instead and the same call can also start at 9:15 or 9:45, which fills a day more densely at the cost of leaving odd gaps behind.</li>
            <li><strong class="text-gray-900 dark:text-white">Active</strong> - untick to stop taking new bookings for a type without deleting it. Existing bookings are unaffected.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Create as many types as you need. A free intro call and a paid consultation can sit side by side with different hours, prices, and rules.</p>
    </section>

    <!-- Weekly hours -->
    <section id="weekly-hours" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Weekly hours
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Tick the days you take bookings and set the hours for each one. A day can hold up to four ranges, so mornings and late afternoons can be open while the middle of the day is not. Ranges cannot overlap.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Hours are in your <strong class="text-gray-900 dark:text-white">schedule's timezone</strong>. Guests always see open times converted to their own timezone, with your timezone shown alongside so nobody has to do the maths.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Set your timezone first</div>
            <p>If your schedule has no timezone set, slots are worked out in the application's default timezone and the Appointments tab warns you. Set it under your schedule's Details before you share your booking page.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Open times also respect what is already on your schedule. Any event that overlaps a slot removes it, including events synced in from Google Calendar, Outlook, or CalDAV, and bookings for your other appointment types.</p>
    </section>

    <!-- Date overrides -->
    <section id="date-overrides" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Date overrides
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Weekly hours repeat every week, so use a date override when one particular day is different. Pick the date, then either leave it marked <strong class="text-gray-900 dark:text-white">Unavailable</strong> to close it completely, or untick that and set the hours you are open just for that date.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">An override replaces the weekly hours for that date rather than adding to them. Public holidays, a day off, a morning-only Friday before a long weekend, and a one-off late evening are all overrides.</p>
        <div class="doc-callout mb-6">
            <div class="doc-callout-title">Overrides are per appointment type</div>
            <p>Each type keeps its own overrides, so closing a date for one type does not close it for the others. If you take a whole week off, add the override to every type you have active, or deactivate the types instead.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300">Past overrides stay listed so you can tidy them up, and they have no effect on open times. Removing a row puts that date back on its normal weekly hours.</p>
    </section>

    <!-- Buffers and notice -->
    <section id="buffers-and-notice" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Buffers, notice &amp; booking window
        </h2>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Buffer before</strong> and <strong class="text-gray-900 dark:text-white">buffer after</strong> - padding in minutes around each appointment so bookings never touch back to back.</li>
            <li><strong class="text-gray-900 dark:text-white">Minimum notice</strong> - how many hours ahead a guest has to book. Set 12, for example, and nobody can grab a slot this evening.</li>
            <li><strong class="text-gray-900 dark:text-white">Booking window</strong> - how many days into the future guests can book. The default is 60.</li>
        </ul>
    </section>

    <!-- Location -->
    <section id="location" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            Where you meet
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each type says how the appointment happens, and only the matching field is shown:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">In person</strong> - enter the address guests should come to.</li>
            <li><strong class="text-gray-900 dark:text-white">Online</strong> - enter the meeting link. It is attached to the booking, so the guest gets it with their confirmation.</li>
            <li><strong class="text-gray-900 dark:text-white">Phone</strong> - enter the number to call.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Whichever you choose travels with the booking: it shows on the booking page, in the confirmation email, and in the calendar invite.</p>
    </section>

    <!-- Guest details -->
    <section id="guest-details" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            What you ask guests
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every booking collects a <strong class="text-gray-900 dark:text-white">name</strong> and <strong class="text-gray-900 dark:text-white">email address</strong>, which is where the confirmation goes. Two options per type add to that:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Ask for a phone number</strong> - adds an optional phone field.</li>
            <li><strong class="text-gray-900 dark:text-white">Require a phone number</strong> - makes that field mandatory.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Guests can also leave <strong class="text-gray-900 dark:text-white">notes</strong> with anything that helps you prepare. Notes are saved on the booking, so they are there when you open it.</p>
    </section>

    <!-- Payments -->
    <section id="payments" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Payments
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Leave the price at zero for a free type, or set a price and currency to charge for it. Paid types take payment by <strong class="text-gray-900 dark:text-white">Stripe</strong>, a <strong class="text-gray-900 dark:text-white">payment link</strong>, or <strong class="text-gray-900 dark:text-white">cash</strong>.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Free and cash</strong> bookings are held as soon as the guest books.</li>
            <li><strong class="text-gray-900 dark:text-white">Stripe and payment link</strong> bookings ask the guest to complete payment. The slot is held while they do, and the hold expires if they never pay, which frees the time for someone else.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Paid types need a payment method</div>
            <p>A paid type stays hidden from guests until a working payment method is connected, and the Appointments tab tells you which type is being held back. Connect Stripe or add a payment link under <x-link href="{{ route('marketing.docs.account_settings') }}#payments">Account Settings</x-link> to make the type bookable.</p>
        </div>
        <div class="doc-callout doc-callout-warning mb-2">
            <div class="doc-callout-title">Refunds are manual</div>
            <p>Cancelling a paid booking does not move any money. Refund it in Stripe or your payment provider, then mark the sale refunded on the Sales page. The cancellation email to you shows the amount and reference so you have both to hand.</p>
        </div>
    </section>

    <!-- Approval -->
    <section id="approval" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Approval
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turn on <strong class="text-gray-900 dark:text-white">Require approval before confirming</strong> and bookings for that type arrive as requests. The type is labelled "Requires confirmation" on the booking page, and the guest is told nothing is booked until you confirm.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Requests land on your <strong class="text-gray-900 dark:text-white">Requests</strong> tab alongside submitted events. Accept and the guest gets their confirmation and calendar invite; decline and they are told they are welcome to book another time. Either way the slot stays blocked while the request is open, so you cannot double-book yourself while you decide.</p>
        <p class="text-gray-600 dark:text-gray-300">On a paid type that takes card or link payments, accepting does not confirm on its own: the guest is confirmed once the payment goes through.</p>
    </section>

    <!-- Managing bookings -->
    <section id="bookings" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Managing bookings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Switch the Appointments tab to <strong class="text-gray-900 dark:text-white">Bookings</strong> to see what has been booked, filtered by <strong class="text-gray-900 dark:text-white">Upcoming</strong>, <strong class="text-gray-900 dark:text-white">Pending</strong>, <strong class="text-gray-900 dark:text-white">Past</strong>, or <strong class="text-gray-900 dark:text-white">Cancelled</strong>. Open a booking to see the guest's details and notes.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Reschedule</strong> a booking to move it to another time without cancelling it. See <x-link href="#rescheduling">Rescheduling</x-link>.</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel</strong> a booking and the guest is emailed. The slot is released, so someone else can take it.</li>
            <li><strong class="text-gray-900 dark:text-white">New bookings email you too</strong>, whether they are confirmed or waiting for your approval.</li>
            <li><strong class="text-gray-900 dark:text-white">Paid bookings appear on the Sales page</strong>, where you mark refunds and see the revenue with the rest of your sales.</li>
        </ul>
    </section>

    <!-- Rescheduling -->
    <section id="rescheduling" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Rescheduling
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A booking can be moved to another time instead of being cancelled and booked again. The booking keeps its payment, its private link, and its place on your Sales page, so nothing has to be refunded and re-charged to change a time.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Either side can start it:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">The guest</strong> uses <strong class="text-gray-900 dark:text-white">Reschedule</strong> on their private link, picks from the same times anyone else would see, and can do it right up until the appointment starts.</li>
            <li><strong class="text-gray-900 dark:text-white">You</strong> use <strong class="text-gray-900 dark:text-white">Reschedule</strong> on the Bookings row. Your picker ignores the type's minimum notice and how far ahead guests may book, so you can move something to later today or up to a year out. Times that have already passed are not offered, and buffers and times you are already booked still apply, so you cannot double-book yourself.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Whoever did not start the move is emailed about it. The guest's email carries an updated calendar invite that moves the entry already in their calendar rather than adding a second one, and your own connected calendars are updated too. When you move a booking you can add a note for the guest, or choose not to email them at all.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If a guest moves a booking less than a day before its original time, the email to you says so, and the Bookings row is marked <strong class="text-gray-900 dark:text-white">Moved</strong> so it is still obvious later.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">On a type that requires approval</div>
            <p>A guest moving their own booking sends it back to your Requests tab, because you approved one time and not any time. Their old slot is released the moment they pick a new one, and the guest is told that before they commit. A move you make yourself does not need your own approval and stays confirmed.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A booking cannot be moved once it has started, been cancelled, or if it is a card or link booking still waiting on payment: those holds expire on their own clock, so the guest pays first and can then move it freely. There is no limit on how many times a booking can be moved. A booking can be moved straight after it is made, and there is a short pause of a few minutes between one move and the next.</p>
    </section>

    <!-- The booking page -->
    <section id="guest-booking" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            The booking page
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once a type is active, the Appointments tab shows <strong class="text-gray-900 dark:text-white">Your booking page</strong> with the link to copy and share. It lives at <code class="doc-inline-code">/book</code> on your schedule, and each type has its own address underneath it, so you can link straight to one type.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Book a Time</strong> button also appears on your public schedule page. Its wording is editable under Customize, like your other labels.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Guests see each type with its duration and price, then pick a day and a time. Times are grouped into morning, afternoon, and evening, with a shortcut to the next available slot, and shown in the guest's own timezone. After they enter their details they get:</p>
        <ul class="doc-list mb-6">
            <li>A confirmation email with a calendar invite they can add in one click.</li>
            <li>A private link to manage the booking, which they can use to <x-link href="#rescheduling">move it to another time</x-link> or cancel it while the appointment is still in the future.</li>
            <li>A reminder email about 24 hours before it starts.</li>
        </ul>
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
            <li><strong class="text-gray-900 dark:text-white">Email has to work.</strong> On the hosted platform, guests get no confirmations or reminders until the schedule's Email settings are configured, and the Appointments tab warns you about it.</li>
            <li><strong class="text-gray-900 dark:text-white">Reminders only go to confirmed bookings.</strong> A request still waiting on you, or a card booking still waiting on payment, does not get one.</li>
            <li><strong class="text-gray-900 dark:text-white">The same guest cannot double-book.</strong> One email address cannot hold two bookings at the same time on your schedule.</li>
            <li><strong class="text-gray-900 dark:text-white">Deactivate rather than delete.</strong> Turning a type off hides it from guests and keeps everything already booked.</li>
            <li><strong class="text-gray-900 dark:text-white">Not the same as Availability.</strong> The Availability tab tracks whole days your team is free to be booked for events. Appointments sell specific time slots.</li>
            <li><strong class="text-gray-900 dark:text-white">Plan.</strong> Appointment booking requires a Pro plan, and is included on selfhosted deployments.</li>
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
            <li><x-link href="{{ route('marketing.docs.managing_schedules') }}">Managing Schedules</x-link> - the Appointments tab and the Requests tab in context</li>
            <li><x-link href="{{ route('marketing.docs.creating_schedules') }}">Creating Schedules</x-link> - set your timezone, email settings, and calendar sync</li>
            <li><x-link href="{{ route('marketing.docs.tickets') }}">Selling Tickets</x-link> - the Sales page, where paid bookings and refunds live</li>
            <li><x-link href="{{ route('marketing.docs.account_settings') }}">Account Settings</x-link> - connect Stripe so paid types can take payment</li>
        </ul>
    </section>
</x-docs-page>
