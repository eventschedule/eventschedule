<x-marketing-layout>
    <x-slot name="title">Appointments - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Appointments</x-slot>
    <x-slot name="description">Set up Calendly-style appointment booking: create appointment types with weekly hours and buffers, take free or paid bookings, require approval, and manage bookings.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Appointments - Event Schedule",
        "description": "How to offer appointment booking: create appointment types with weekly hours, buffers, and optional payment, and let guests book a time on your public page.",
        "author": { "@type": "Organization", "name": "Event Schedule" },
        "publisher": { "@type": "Organization", "name": "Event Schedule" }
    }
    </script>
    </x-slot>

    <div class="prose dark:prose-invert max-w-none">
        <h1>Appointments</h1>

        <section id="overview">
            <p>Appointment booking lets guests reserve a time with you on a public page, Calendly-style. You define <strong>appointment types</strong> (for example, a 30 minute intro call) with the hours you are available, and guests pick an open slot and book it. Appointment booking is a <strong>Pro</strong> feature, and is included on all selfhosted deployments.</p>
        </section>

        <section id="appointment-types">
            <h2>Appointment types</h2>
            <p>Open the <strong>Appointments</strong> tab on your schedule and choose <em>New appointment type</em>. Each type has a name, a duration, and the weekly hours you take bookings for. You can create several types (a free intro call, a paid consultation, and so on). Toggle a type inactive to stop taking new bookings without deleting it.</p>
        </section>

        <section id="weekly-hours">
            <h2>Weekly hours</h2>
            <p>Set the hours you are open for bookings on each day of the week. You can add more than one range per day (for example, mornings and late afternoons). Hours are in your schedule's timezone, so set your timezone first under the schedule's Details. Guests always see open times converted to their own timezone.</p>
        </section>

        <section id="buffers-and-notice">
            <h2>Buffers, notice, and booking window</h2>
            <ul>
                <li><strong>Buffers</strong> add padding before and after each appointment so bookings never touch back to back.</li>
                <li><strong>Minimum notice</strong> stops guests from booking too soon (for example, at least 12 hours ahead).</li>
                <li><strong>Booking window</strong> limits how far into the future guests can book.</li>
            </ul>
            <p>Open times also respect the events already on your connected Google, Outlook, or CalDAV calendar, so you will not be double-booked.</p>
        </section>

        <section id="payments">
            <h2>Payments</h2>
            <p>Appointment types can be free or paid. For paid types, choose a price, currency, and payment method (Stripe, a payment URL, or cash). Free bookings confirm instantly. Paid types are only shown to guests once a working payment method is connected.</p>
        </section>

        <section id="approval">
            <h2>Approval</h2>
            <p>Turn on <em>Require approval</em> and new bookings arrive as requests. Guests are told the time is not confirmed until you approve it. Accept or decline requests from the Requests tab; guests are emailed either way.</p>
        </section>

        <section id="bookings">
            <h2>Managing bookings</h2>
            <p>The <strong>Bookings</strong> view lists your appointments with filters for upcoming, pending, past, and cancelled. You can cancel a booking (the guest is emailed) or open it to see the details. Cancelling an appointment frees the slot so someone else can book it.</p>
        </section>

        <section id="guest-booking">
            <h2>The guest booking page</h2>
            <p>Share your booking page from the <em>Book a Time</em> button on your schedule, or link to it directly. Guests pick a date and time, enter their details, and book. They receive a confirmation email with a calendar invite and a link to manage or cancel, plus a reminder before the appointment.</p>
        </section>
    </div>
</x-marketing-layout>
