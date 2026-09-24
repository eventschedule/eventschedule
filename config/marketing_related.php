<?php

/**
 * Related marketing pages map.
 *
 * Keyed by request path (e.g. 'features/ticketing', 'for-musicians', 'pricing').
 * Each value is an array of 3-6 related entries with: title, path, blurb.
 *
 * Used by <x-marketing.related-pages /> to render a "Related" strip above the
 * footer. Adding a new key here automatically enables the strip on that page.
 *
 * A key with no matching view renders nothing and is dead weight; a view that
 * invokes the component with no matching key renders an empty strip. Both are
 * failures in tests/Feature/MarketingRelatedPagesTest.php, which also checks
 * that every path is a registered marketing route that answers 200.
 *
 * Blurbs are the target page's own words, shortened. Never write a capability
 * onto a card that the page it points at does not already claim.
 */

return [
    // The parent page for everything ticketing, and it linked to none of the five pages that break
    // out one part of it. FOUR entries, like 111 of the 112 keys here: MarketingRelatedPagesTest
    // caps a strip at 6, and four is the only size that also fills every row at both
    // sm:grid-cols-2 and the lg:grid-cols-{min(count, 4)} the component derives.
    // Four cannot hold all five, so the three chosen are the ones that make the other two
    // REACHABLE: promo-codes carries installments and waitlist in its own strip, so every new page
    // is within two hops of here. Allocated seating moved one hop out (check-in links it) and the
    // Eventbrite comparison is still reached from /compare and from its twenty-five siblings.
    // The fourth slot, once Pricing, is /features/registration: the free, no-payment half of the
    // same Tickets panel, which this page describes only in passing.
    'features/ticketing' => [
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Check-in Dashboard', 'path' => '/features/check-in', 'blurb' => 'Watch the room fill up while you are standing at the door.'],
        ['title' => 'Promo Codes & Add-ons', 'path' => '/features/promo-codes', 'blurb' => 'Discounts that expire and cap themselves, and extras with their own stock.'],
        ['title' => 'Free Registration & RSVP', 'path' => '/features/registration', 'blurb' => 'Free sign-ups with a cap per date, a waitlist and a QR code, on every plan.'],
    ],

    'paypal' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Stripe', 'path' => '/stripe', 'blurb' => 'Card payments straight into your own Stripe account.'],
        ['title' => 'Integrations', 'path' => '/features/integrations', 'blurb' => 'Every port in and out of Event Schedule, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    // The first card used to promise "a calculator" on /eventbrite-alternative, which has none. The
    // calculator is its own page now; the head-to-head is linked twice from this page's body.
    'switch-from-eventbrite' => [
        ['title' => 'Ticket Fee Calculator', 'path' => '/ticket-fee-calculator', 'blurb' => 'What Eventbrite and seven other platforms take from your ticket sales, at their published rates.'],
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Stripe', 'path' => '/stripe', 'blurb' => 'Card payments straight into your own Stripe account.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/promo-codes' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Ticket Waitlist', 'path' => '/features/waitlist', 'blurb' => 'A sold-out date offers a returned seat to one person at a time.'],
        ['title' => 'Installment Payments', 'path' => '/features/installments', 'blurb' => 'Let buyers spread an expensive ticket over monthly payments.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/waitlist' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Check-in Dashboard', 'path' => '/features/check-in', 'blurb' => 'Watch the room fill up while you are standing at the door.'],
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Free Registration & RSVP', 'path' => '/features/registration', 'blurb' => 'Free sign-ups with a cap per date, a waitlist and a QR code, on every plan.'],
    ],

    'features/installments' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Stripe', 'path' => '/stripe', 'blurb' => 'Card payments straight into your own Stripe account.'],
        ['title' => 'Promo Codes & Add-ons', 'path' => '/features/promo-codes', 'blurb' => 'Discounts that expire and cap themselves, and extras with their own stock.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/check-in' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Allocated Seating', 'path' => '/features/allocated-seating', 'blurb' => 'Draw your room once and let buyers pick their own seats from a map of it.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/passes' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Subscriptions & Passes Guide', 'path' => '/docs/subscriptions', 'blurb' => 'Create a pass, choose what it covers, and watch the visits come off it.'],
        ['title' => 'For Fitness & Yoga', 'path' => '/for-fitness-and-yoga', 'blurb' => 'Class packs and memberships for a studio that runs most days.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/allocated-seating' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'For Theaters', 'path' => '/for-theaters', 'blurb' => 'Run a season, a run and a house from one schedule.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run every Enterprise feature on your own server at no cost.'],
    ],

    'features' => [
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run every Enterprise feature on your own server at no cost.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Examples', 'path' => '/examples', 'blurb' => 'Real schedules built by venues, artists, and organizers.'],
    ],

    'use-cases' => [
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, whatever kind of events you run.'],
        ['title' => 'Examples', 'path' => '/examples', 'blurb' => 'Explore live demo schedules built for different industries.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'Free forever, with zero platform fees on ticket sales.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
    ],

    'pricing' => [
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run Event Schedule on your own server at no cost.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
    ],

    'ticket-fee-calculator' => [
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Zero platform fees instead of 3.7% + $1.79 a ticket, paid to your own Stripe or PayPal.'],
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'compare' => [
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'What each plan costs, with no platform fees on any of them.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Everything the comparison grid is measuring, explained.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run it on your own server with every paid feature included.'],
    ],

    'selfhost' => [
        ['title' => 'White-Label SaaS', 'path' => '/saas', 'blurb' => 'Turn your install into a ticketing business you own.'],
        ['title' => 'Pretix Alternative', 'path' => '/pretix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus AI features.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'What the hosted plans cost, if you would rather not run a server.'],
        ['title' => 'Open Source', 'path' => '/open-source', 'blurb' => 'The licence, the repositories and how to contribute.'],
    ],

    'saas' => [
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Install Event Schedule on your own server with Docker or Softaculous.'],
        ['title' => 'White Label', 'path' => '/features/white-label', 'blurb' => 'Remove branding and make the platform look like your product.'],
        ['title' => 'Open Source', 'path' => '/open-source', 'blurb' => 'Read, fork, and contribute to the code you build your business on.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
    ],

    'features/ai' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way Google Calendar and CalDAV sync.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate flyers and share graphics from your events.'],
        ['title' => 'For AI Agents', 'path' => '/for-ai-agents', 'blurb' => 'Expose your schedule as structured data for AI agents.'],
    ],

    'features/calendar-sync' => [
        ['title' => 'Google Calendar', 'path' => '/google-calendar', 'blurb' => 'How the two-way Google Calendar integration works.'],
        ['title' => 'CalDAV', 'path' => '/caldav', 'blurb' => 'Sync with any CalDAV-compatible calendar server.'],
        ['title' => 'AddEvent Alternative', 'path' => '/addevent-alternative', 'blurb' => 'Ticketing and public event pages, not just calendar buttons.'],
        ['title' => 'Google Calendar Alternative', 'path' => '/google-calendar-alternative', 'blurb' => 'When a Google Calendar link is not enough.'],
    ],

    'for-musicians' => [
        ['title' => 'For DJs', 'path' => '/for-djs', 'blurb' => 'DJ sets, residencies, and guest spots in one place.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Fill the calendar at the venues you play.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to your shows with zero platform fees.'],
        ['title' => 'Bandsintown Alternative', 'path' => '/bandsintown-alternative', 'blurb' => 'Your own tour-date page with ticketing and calendar sync.'],
    ],

    'for-djs' => [
        ['title' => 'For Musicians', 'path' => '/for-musicians', 'blurb' => 'Tour dates, gigs, and fans on one link.'],
        ['title' => 'For Nightclubs', 'path' => '/for-nightclubs', 'blurb' => 'Run the room your sets light up.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell advance tickets with zero platform fees.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate set-time flyers for your socials.'],
    ],

    'for-comedians' => [
        ['title' => 'For Comedy Clubs', 'path' => '/for-comedy-clubs', 'blurb' => 'Run the room where you get your reps.'],
        ['title' => 'For Spoken Word', 'path' => '/for-spoken-word', 'blurb' => 'Open mics and features for poets and storytellers.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to your shows with zero platform fees.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate show flyers for your socials.'],
    ],

    // The for-spoken-word page already links the neighbouring audience pages
    // inline, so this strip carries the features an open mic host reaches for.
    'for-spoken-word' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set the weekly mic once, and skip the weeks you are closed.'],
        ['title' => 'Custom Fields', 'path' => '/features/custom-fields', 'blurb' => 'Ask what they are reading right on the sign-up form.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep the mic, the reading series, and workshops apart.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the regulars when the next night is up.'],
    ],

    'for-circus-acrobatics' => [
        ['title' => 'For Magicians', 'path' => '/for-magicians', 'blurb' => 'Shows, residencies, and private bookings on one schedule.'],
        ['title' => 'For Dance Groups', 'path' => '/for-dance-groups', 'blurb' => 'Rehearsals, recitals, and touring dates in one place.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to your shows with zero platform fees.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate show posters for your socials.'],
    ],

    'for-magicians' => [
        ['title' => 'For Comedians', 'path' => '/for-comedians', 'blurb' => 'Mics, guest sets, and headline dates on one link.'],
        ['title' => 'For Circus & Acrobatics', 'path' => '/for-circus-acrobatics', 'blurb' => 'Shows, residencies, and private bookings in one place.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to your shows with zero platform fees.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate show posters for your socials.'],
    ],

    'for-talent' => [
        ['title' => 'For Venues', 'path' => '/for-venues', 'blurb' => 'The other side of the booking: how venues build a lineup.'],
        ['title' => 'For Curators', 'path' => '/for-curators', 'blurb' => 'Run a festival or multi-artist bill across many schedules.'],
        ['title' => 'Booking Requests', 'path' => '/features/booking-requests', 'blurb' => 'Promoters and venues ask to book you from your own page, free.'],
        ['title' => 'Linktree Replacement', 'path' => '/linktree-replacement', 'blurb' => 'One bio link that shows your actual dates, not just buttons.'],
    ],

    // The for-curators page already links the neighbouring audience pages inline,
    // so this strip carries the features a curator reaches for instead.
    'for-curators' => [
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Sort what arrives into the sections of your guide.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Drop the guide into the site you already have.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => "Send the week's highlights to your subscribers."],
        ['title' => 'AI Features', 'path' => '/features/ai', 'blurb' => 'Turn pasted text or a photo of a flyer into a listed event.'],
    ],

    'for-venues' => [
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run every show from one schedule.'],
        ['title' => 'For Bars & Pubs', 'path' => '/for-bars', 'blurb' => 'Fill the room with trivia, bands, and events.'],
        ['title' => 'Booking Requests', 'path' => '/features/booking-requests', 'blurb' => 'Acts ask you for a date on a form of your own.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Why venues are moving off Eventbrite.'],
    ],

    // The for-theaters page already links the neighbouring audience pages
    // inline, so this strip carries the features a run reaches for, plus the Brown Paper
    // Tickets comparison: BPT is being retired and many small theaters sold through it.
    'for-theaters' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a run once, with dark days and a closing performance.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Named ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep mainstage, studio and family programming apart.'],
        ['title' => 'Brown Paper Tickets Alternative', 'path' => '/brown-paper-tickets-alternative', 'blurb' => 'Brown Paper Tickets is being retired: zero platform fees and unlimited free registration.'],
    ],

    // The for-music-venues page already links the neighbouring audience pages
    // inline, so this strip carries the features a show day reaches for.
    'for-music-venues' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => "Keep each room's listings apart on one link."],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a residency once, and skip the weeks you are dark.'],
        ['title' => 'Booking Requests', 'path' => '/features/booking-requests', 'blurb' => 'Bands ask for a date on a form whose fields you choose.'],
    ],

    // The for-nightclubs page already links the neighbouring audience pages
    // inline, so this strip carries the features the door side reaches for.
    'for-nightclubs' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a residency once, and skip the weeks you close.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep every night in its own lane on one link.'],
        ['title' => 'Posh Alternative', 'path' => '/posh-alternative', 'blurb' => 'Ticketing for parties and nights out with zero platform fees.'],
    ],

    // The for-art-galleries page already links the neighbouring audience pages
    // inline, and its own Key features block covers recurring events,
    // sub-schedules, custom fields and embed, so this strip carries the rest of
    // what a gallery reaches for across a run.
    'for-art-galleries' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'For the collector dinner: a capacity, QR check-in and zero platform fees.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google, Outlook and CalDAV.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Tell the collectors yourself, within a monthly allowance counted per recipient.'],
        ['title' => 'Analytics', 'path' => '/features/analytics', 'blurb' => 'See which evening of the run people are actually looking at.'],
    ],

    // The for-breweries-and-wineries page already links the neighbouring
    // audience pages inline, and its own Key features block covers recurring
    // events, sub-schedules, ticketing and embed, so this strip carries the
    // rest of what a taproom reaches for.
    'for-breweries-and-wineries' => [
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google, Outlook and CalDAV.'],
        ['title' => 'Analytics', 'path' => '/features/analytics', 'blurb' => 'See which nights people are actually looking at.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Generate the post for Friday without opening a design app.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email your followers about a release, within a monthly allowance counted per recipient.'],
    ],

    // The for-restaurants page already links the neighbouring audience pages
    // inline, so this strip carries the features a ticketed sitting needs.
    'for-restaurants' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'A fixed covers count, a sales cutoff, and zero platform fees.'],
        ['title' => 'Custom Fields', 'path' => '/features/custom-fields', 'blurb' => 'Ask for allergies or a course choice at checkout.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Give private dining its own strand and its own link.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Tell the regulars before the seats are gone.'],
    ],

    // The for-comedy-clubs page already links the neighbouring audience pages
    // inline, so this strip carries the features a room reaches for.
    'for-comedy-clubs' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a weekly night once, and take out the weeks you are dark.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Advance and door pricing, QR check-in, and zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep the open mic, the showcase and the weekend apart.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the people who follow the room, with open rates.'],
    ],

    // The for-food-trucks-and-vendors page already links the neighbouring
    // audience pages inline, so this strip carries the features a route needs.
    'for-food-trucks-and-vendors' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a regular pitch once, and take out the weeks you lose it.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => "Send the week's route to the people who follow you."],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Drop the route into the website you already have.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep markets, festivals and private hire apart on one link.'],
    ],

    // The for-theater-performers page already links the neighbouring audience
    // pages inline, so this strip carries the features a performer reaches for.
    'for-theater-performers' => [
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep productions, teaching and auditions apart on one link.'],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a run once, with a closing performance.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the people who follow you, with open rates.'],
    ],

    // The for-dance-groups page already links the neighbouring audience pages
    // inline, so this strip carries the features a studio week reaches for.
    'for-dance-groups' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a weekly class once, and skip the weeks you are closed.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Class cards, memberships and show tickets with zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep classes, rehearsals and performances apart on one link.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the people who follow your studio, with open rates.'],
    ],

    'for-bars' => [
        ['title' => 'For Restaurants', 'path' => '/for-restaurants', 'blurb' => 'Fill every seat with events and tastings.'],
        ['title' => 'For Breweries & Wineries', 'path' => '/for-breweries-and-wineries', 'blurb' => 'Run tasting rooms and release parties.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to ticketed events with zero platform fees.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run your live music calendar in one place.'],
    ],
    'about' => [
        ['title' => 'Open Source', 'path' => '/open-source', 'blurb' => 'The licence, the repositories and how to contribute.'],
        ['title' => 'Contact Us', 'path' => '/contact', 'blurb' => 'Email support, GitHub issues, and where else to find us.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'accessibility' => [
        ['title' => 'Accessibility Guide', 'path' => '/docs/selfhost/accessibility', 'blurb' => 'The accessibility options built into every schedule.'],
        ['title' => 'Privacy Policy', 'path' => '/privacy', 'blurb' => 'How we collect, use, and protect your data.'],
        ['title' => 'About Event Schedule', 'path' => '/about', 'blurb' => 'Who builds Event Schedule, and why it is open source.'],
        ['title' => 'Contact Us', 'path' => '/contact', 'blurb' => 'Email support, GitHub issues, and where else to find us.'],
    ],

    'browse' => [
        ['title' => 'Examples', 'path' => '/examples', 'blurb' => 'Real schedules built by venues, artists, and organizers.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'contact' => [
        ['title' => 'FAQ', 'path' => '/faq', 'blurb' => 'Answers on pricing, ticketing, calendar sync and selfhosting.'],
        ['title' => 'Documentation', 'path' => '/docs', 'blurb' => 'The user guide, the selfhost notes and the API reference.'],
        ['title' => 'About Event Schedule', 'path' => '/about', 'blurb' => 'Who builds Event Schedule, and why it is open source.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'examples' => [
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Browse Events', 'path' => '/browse', 'blurb' => 'Upcoming live music, comedy, classes and markets, soonest first.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'faq' => [
        ['title' => 'Documentation', 'path' => '/docs', 'blurb' => 'The user guide, the selfhost notes and the API reference.'],
        ['title' => 'Why Create an Account', 'path' => '/why-create-account', 'blurb' => 'What a free account unlocks, with no credit card required.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'open-source' => [
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run every Enterprise feature on your own server at no cost.'],
        ['title' => 'White-Label SaaS', 'path' => '/saas', 'blurb' => 'Turn your install into a ticketing business you own.'],
        ['title' => 'Pretix Alternative', 'path' => '/pretix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus AI features.'],
        ['title' => 'Hi.Events Alternative', 'path' => '/hi-events-alternative', 'blurb' => 'Two open source ticketing platforms, compared line by line.'],
    ],

    'privacy' => [
        ['title' => 'Terms of Service', 'path' => '/terms-of-service', 'blurb' => 'The rules and guidelines for using the platform.'],
        ['title' => 'About Event Schedule', 'path' => '/about', 'blurb' => 'Who builds Event Schedule, and why it is open source.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run every Enterprise feature on your own server at no cost.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'terms-of-service' => [
        ['title' => 'Privacy Policy', 'path' => '/privacy', 'blurb' => 'How we collect, use, and protect your data.'],
        ['title' => 'About Event Schedule', 'path' => '/about', 'blurb' => 'Who builds Event Schedule, and why it is open source.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'self-hosting-terms-of-service' => [
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run every Enterprise feature on your own server at no cost.'],
        ['title' => 'Open Source', 'path' => '/open-source', 'blurb' => 'The licence, the repositories and how to contribute.'],
        ['title' => 'Selfhost Guide', 'path' => '/docs/selfhost', 'blurb' => 'Installing and running Event Schedule on your own server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'why-create-account' => [
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'FAQ', 'path' => '/faq', 'blurb' => 'Answers on pricing, ticketing, calendar sync and selfhosting.'],
        ['title' => 'Examples', 'path' => '/examples', 'blurb' => 'Real schedules built by venues, artists, and organizers.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/analytics' => [
        ['title' => 'Boost', 'path' => '/features/boost', 'blurb' => 'Turn your event details into live Facebook and Instagram ads.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Send branded newsletters to your followers and ticket buyers.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/availability' => [
        ['title' => 'Team Scheduling', 'path' => '/features/team-scheduling', 'blurb' => 'Put other people on your schedule with a named position.'],
        ['title' => 'Appointments', 'path' => '/features/appointments', 'blurb' => 'Write your hours down once and let guests pick an open time.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/carpool' => [
        ['title' => 'Custom Fields', 'path' => '/features/custom-fields', 'blurb' => 'Ask your own questions on the ticket and registration forms.'],
        ['title' => 'Feedback', 'path' => '/features/feedback', 'blurb' => 'A rating and an optional comment from everyone who booked.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/custom-css' => [
        ['title' => 'White Label', 'path' => '/features/white-label', 'blurb' => 'Remove branding and make the platform look like your product.'],
        ['title' => 'Custom Domain', 'path' => '/features/custom-domain', 'blurb' => 'Use your own domain instead of the default subdomain.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/custom-domain' => [
        ['title' => 'White Label', 'path' => '/features/white-label', 'blurb' => 'Remove branding and make the platform look like your product.'],
        ['title' => 'Custom CSS', 'path' => '/features/custom-css', 'blurb' => 'Write your own CSS and customize every pixel of your schedule.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/custom-fields' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Custom Labels', 'path' => '/features/custom-labels', 'blurb' => 'Rename the words on your public schedule, across 34 labels.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/custom-labels' => [
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Sort one schedule into named, coloured sections.'],
        ['title' => 'Custom CSS', 'path' => '/features/custom-css', 'blurb' => 'Write your own CSS and customize every pixel of your schedule.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/embed-tickets' => [
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'WordPress Event Calendar', 'path' => '/wordpress-event-calendar', 'blurb' => 'Put your live calendar on a WordPress page with one Custom HTML block.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/event-graphics' => [
        ['title' => 'AI Features', 'path' => '/features/ai', 'blurb' => 'Parse events from text, generate flyers, and translate content with AI.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Send branded newsletters to your followers and ticket buyers.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/feedback' => [
        ['title' => 'Polls', 'path' => '/features/polls', 'blurb' => 'Add a poll to any event: a question and two to ten choices.'],
        ['title' => 'Fan Videos', 'path' => '/features/fan-videos', 'blurb' => 'Let fans add YouTube videos, photos and comments to your event pages.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/gift-cards' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Embed Tickets', 'path' => '/features/embed-tickets', 'blurb' => 'Put the ticket checkout on your own website with one iframe tag.'],
        ['title' => 'Gift Cards Guide', 'path' => '/docs/gift-cards', 'blurb' => 'Set denominations, send a card by email, and redeem it at checkout.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/integrations' => [
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'Stripe', 'path' => '/stripe', 'blurb' => 'The charge is created on your own account, with no platform fee.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/newsletters' => [
        ['title' => 'Analytics', 'path' => '/features/analytics', 'blurb' => 'Page views, traffic sources and devices, with no external service.'],
        ['title' => 'Boost', 'path' => '/features/boost', 'blurb' => 'Turn your event details into live Facebook and Instagram ads.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/online-events' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/polls' => [
        ['title' => 'Feedback', 'path' => '/features/feedback', 'blurb' => 'A rating and an optional comment from everyone who booked.'],
        ['title' => 'Fan Videos', 'path' => '/features/fan-videos', 'blurb' => 'Let fans add YouTube videos, photos and comments to your event pages.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/private-events' => [
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Sort one schedule into named, coloured sections.'],
        ['title' => 'Custom Domain', 'path' => '/features/custom-domain', 'blurb' => 'Use your own domain instead of the default subdomain.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/recurring-events' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'For Sports Leagues', 'path' => '/for-sports-leagues', 'blurb' => 'A fixture list per team, one season calendar for the league, and rides to away games.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/sub-schedules' => [
        ['title' => 'Custom Labels', 'path' => '/features/custom-labels', 'blurb' => 'Rename the words on your public schedule, across 34 labels.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'Community Event Calendar', 'path' => '/community-event-calendar', 'blurb' => 'One shared calendar for a town, fed by the organizers in it.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/team-scheduling' => [
        ['title' => 'Availability', 'path' => '/features/availability', 'blurb' => 'Mark whole dates as unavailable so your team sees who is out.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/white-label' => [
        ['title' => 'Custom Domain', 'path' => '/features/custom-domain', 'blurb' => 'Use your own domain instead of the default subdomain.'],
        ['title' => 'Custom CSS', 'path' => '/features/custom-css', 'blurb' => 'Write your own CSS and customize every pixel of your schedule.'],
        ['title' => 'White-Label SaaS', 'path' => '/saas', 'blurb' => 'Turn your install into a ticketing business you own.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/appointments' => [
        ['title' => 'Availability', 'path' => '/features/availability', 'blurb' => 'Mark whole dates as unavailable so your team sees who is out.'],
        ['title' => 'Team Scheduling', 'path' => '/features/team-scheduling', 'blurb' => 'Put other people on your schedule with a named position.'],
        ['title' => 'Custom Fields', 'path' => '/features/custom-fields', 'blurb' => 'Ask your own questions on the ticket and registration forms.'],
        ['title' => 'Calendly Replacement', 'path' => '/calendly-replacement', 'blurb' => 'One-on-one bookings plus public events with ticketing.'],
    ],

    'features/boost' => [
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate flyers and share graphics from your events.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Send branded newsletters to your followers and ticket buyers.'],
        ['title' => 'Analytics', 'path' => '/features/analytics', 'blurb' => 'Page views, traffic sources and devices, with no external service.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/embed-calendar' => [
        ['title' => 'Embed Tickets', 'path' => '/features/embed-tickets', 'blurb' => 'Put the ticket checkout on your own website with one iframe tag.'],
        ['title' => 'Custom CSS', 'path' => '/features/custom-css', 'blurb' => 'Write your own CSS and customize every pixel of your schedule.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'The Events Calendar Alternative', 'path' => '/the-events-calendar-alternative', 'blurb' => 'Event pages, ticketing and calendar sync without a WordPress plugin.'],
    ],

    'features/fan-videos' => [
        ['title' => 'Feedback', 'path' => '/features/feedback', 'blurb' => 'A rating and an optional comment from everyone who booked.'],
        ['title' => 'Polls', 'path' => '/features/polls', 'blurb' => 'Add a poll to any event: a question and two to ten choices.'],
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate flyers and share graphics from your events.'],
        ['title' => 'For Musicians', 'path' => '/for-musicians', 'blurb' => 'Tour dates, gigs, and fans on one link.'],
    ],

    'caldav' => [
        ['title' => 'Google Calendar', 'path' => '/google-calendar', 'blurb' => 'How the two-way Google Calendar integration works.'],
        ['title' => 'Outlook Calendar', 'path' => '/outlook-calendar', 'blurb' => 'Two-way sync with Outlook and Microsoft 365, plus Teams links.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'google-calendar' => [
        ['title' => 'Outlook Calendar', 'path' => '/outlook-calendar', 'blurb' => 'Two-way sync with Outlook and Microsoft 365, plus Teams links.'],
        ['title' => 'CalDAV', 'path' => '/caldav', 'blurb' => 'Sync with any CalDAV-compatible calendar server.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'outlook-calendar' => [
        ['title' => 'Google Calendar', 'path' => '/google-calendar', 'blurb' => 'How the two-way Google Calendar integration works.'],
        ['title' => 'CalDAV', 'path' => '/caldav', 'blurb' => 'Sync with any CalDAV-compatible calendar server.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar, Outlook and any CalDAV server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'stripe' => [
        ['title' => 'Invoice Ninja', 'path' => '/invoiceninja', 'blurb' => 'Every ticket sale lands in Invoice Ninja as an invoice.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Integrations', 'path' => '/features/integrations', 'blurb' => 'Google Calendar, Outlook, CalDAV, Stripe, webhooks and the REST API.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'invoiceninja' => [
        ['title' => 'Stripe', 'path' => '/stripe', 'blurb' => 'The charge is created on your own account, with no platform fee.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Integrations', 'path' => '/features/integrations', 'blurb' => 'Google Calendar, Outlook, CalDAV, Stripe, webhooks and the REST API.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-ai-agents' => [
        ['title' => 'Integrations', 'path' => '/features/integrations', 'blurb' => 'Google Calendar, Outlook, CalDAV, Stripe, webhooks and the REST API.'],
        ['title' => 'Open Source', 'path' => '/open-source', 'blurb' => 'The licence, the repositories and how to contribute.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-community-centers' => [
        ['title' => 'For Libraries', 'path' => '/for-libraries', 'blurb' => 'Set story time up once and give every date its own place count.'],
        ['title' => 'For Farmers Markets', 'path' => '/for-farmers-markets', 'blurb' => 'Market days, vendor lineups, and seasonal events.'],
        ['title' => 'Humanitix Alternative', 'path' => '/humanitix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus selfhosting.'],
        ['title' => 'Facebook Events Alternative', 'path' => '/facebook-events-alternative', 'blurb' => 'An events page you own, with ticketing and calendar sync.'],
    ],

    'for-farmers-markets' => [
        ['title' => 'For Food Trucks', 'path' => '/for-food-trucks-and-vendors', 'blurb' => "One link that always has today's stop."],
        ['title' => 'For Community Centers', 'path' => '/for-community-centers', 'blurb' => 'Programs, classes, hall-hire requests and events in one place.'],
        ['title' => 'For Festivals', 'path' => '/for-festivals', 'blurb' => 'Stages as sub-schedules, sets in the running order, and a weekend pass on one QR.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-hotels-and-resorts' => [
        ['title' => 'For Restaurants', 'path' => '/for-restaurants', 'blurb' => 'Fill every seat with events and tastings.'],
        ['title' => 'For Fitness & Yoga', 'path' => '/for-fitness-and-yoga', 'blurb' => 'Share your class schedule and sell drop-ins and class passes.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-libraries' => [
        ['title' => 'For Community Centers', 'path' => '/for-community-centers', 'blurb' => 'Programs, classes, hall-hire requests and events in one place.'],
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'For Museums', 'path' => '/for-museums', 'blurb' => 'Tours, talks and family days on top of the galleries that are simply open.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-churches' => [
        ['title' => 'For Nonprofits', 'path' => '/for-nonprofits', 'blurb' => 'Galas, volunteer days and campaigns, with zero platform fees on every ticket.'],
        ['title' => 'For Community Centers', 'path' => '/for-community-centers', 'blurb' => 'Programs, classes, hall-hire requests and events in one place.'],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set the weekly service once, and take out the Sundays it moves.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Write to the congregation yourself, within an allowance counted per recipient.'],
    ],

    'for-schools' => [
        ['title' => 'For Libraries', 'path' => '/for-libraries', 'blurb' => 'Set story time up once and give every date its own place count.'],
        ['title' => 'Appointments', 'path' => '/features/appointments', 'blurb' => 'Write your hours down once and let guests pick an open time.'],
        ['title' => 'For Sports Leagues', 'path' => '/for-sports-leagues', 'blurb' => 'A fixture list per team, one season calendar for the league, and rides to away games.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep each year group, club and team on its own link.'],
    ],

    'for-nonprofits' => [
        ['title' => 'Zeffy Alternative', 'path' => '/zeffy-alternative', 'blurb' => 'Open source ticketing for any organizer, with zero platform fees.'],
        ['title' => 'Humanitix Alternative', 'path' => '/humanitix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus selfhosting.'],
        ['title' => 'For Churches', 'path' => '/for-churches', 'blurb' => 'Sunday set once, and every group, rehearsal and sign-up on the same link.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Write to supporters yourself, within an allowance counted per recipient.'],
    ],

    'for-festivals' => [
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'For Food Trucks', 'path' => '/for-food-trucks-and-vendors', 'blurb' => "One link that always has today's stop."],
        ['title' => 'For Farmers Markets', 'path' => '/for-farmers-markets', 'blurb' => 'Market days, vendor lineups, and seasonal events.'],
        ['title' => 'For Curators', 'path' => '/for-curators', 'blurb' => 'Run a festival or multi-artist bill across many schedules.'],
    ],

    'for-sports-leagues' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set the weekly training session once, and skip the weeks the pitch is closed.'],
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Carpool', 'path' => '/features/carpool', 'blurb' => 'Let families offer and ask for a seat to the away game.'],
        ['title' => 'For Schools', 'path' => '/for-schools', 'blurb' => 'Term dates, the school play and parent evenings on one calendar families subscribe to.'],
    ],

    'for-museums' => [
        ['title' => 'For Art Galleries', 'path' => '/for-art-galleries', 'blurb' => 'A six-week hang is one recurring event, not thirty entries.'],
        ['title' => 'For Libraries', 'path' => '/for-libraries', 'blurb' => 'Set story time up once and give every date its own place count.'],
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Appointments', 'path' => '/features/appointments', 'blurb' => 'Write your hours down once and let guests pick an open time.'],
    ],

    'for-meetup-groups' => [
        ['title' => 'Meetup Alternative', 'path' => '/meetup-alternative', 'blurb' => 'Zero platform fees and custom domains, without a subscription.'],
        ['title' => 'Mobilizon Alternative', 'path' => '/mobilizon-alternative', 'blurb' => 'Open source events with ticketing and calendar sync built in.'],
        ['title' => 'For Curators', 'path' => '/for-curators', 'blurb' => 'Run a festival or multi-artist bill across many schedules.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Write to members yourself, within an allowance counted per recipient.'],
    ],

    'for-online-classes' => [
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'For Webinars', 'path' => '/for-webinars', 'blurb' => 'Registration, ticketing, and a join link on any platform.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-virtual-conferences' => [
        ['title' => 'For Webinars', 'path' => '/for-webinars', 'blurb' => 'Registration, ticketing, and a join link on any platform.'],
        ['title' => 'For Live Q&A Sessions', 'path' => '/for-live-qa-sessions', 'blurb' => 'Registration, ticketing and email for a live Q&A on any platform.'],
        ['title' => 'Sched Alternative', 'path' => '/sched-alternative', 'blurb' => 'Zero platform fees, calendar sync, and open source flexibility.'],
        ['title' => 'Splash Alternative', 'path' => '/splash-alternative', 'blurb' => 'Zero platform fees and open source, without enterprise pricing.'],
    ],

    'for-visual-artists' => [
        ['title' => 'For Art Galleries', 'path' => '/for-art-galleries', 'blurb' => 'A six-week hang is one recurring event, not thirty entries.'],
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-watch-parties' => [
        ['title' => 'For Live Q&A Sessions', 'path' => '/for-live-qa-sessions', 'blurb' => 'Registration, ticketing and email for a live Q&A on any platform.'],
        ['title' => 'For Online Classes', 'path' => '/for-online-classes', 'blurb' => 'Sell online classes with registration and recurring sessions.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-workshop-instructors' => [
        ['title' => 'For Online Classes', 'path' => '/for-online-classes', 'blurb' => 'Sell online classes with registration and recurring sessions.'],
        ['title' => 'For Fitness & Yoga', 'path' => '/for-fitness-and-yoga', 'blurb' => 'Share your class schedule and sell drop-ins and class passes.'],
        ['title' => 'For Schools', 'path' => '/for-schools', 'blurb' => 'Term dates, the school play and parent evenings on one calendar families subscribe to.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-fitness-and-yoga' => [
        ['title' => 'Passes & Subscriptions', 'path' => '/features/passes', 'blurb' => 'One pass, many events, counted down on a single QR code.'],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a weekly class once, and skip the weeks you are closed.'],
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'For Online Classes', 'path' => '/for-online-classes', 'blurb' => 'Sell online classes with registration and recurring sessions.'],
    ],

    'for-live-concerts' => [
        ['title' => 'For Musicians', 'path' => '/for-musicians', 'blurb' => 'Tour dates, gigs, and fans on one link.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run your live music calendar in one place.'],
        ['title' => 'Online Events', 'path' => '/features/online-events', 'blurb' => 'Paste a Zoom, Meet or stream link once and the ticket and listing follow.'],
        ['title' => 'Brown Paper Tickets Alternative', 'path' => '/brown-paper-tickets-alternative', 'blurb' => 'Brown Paper Tickets is being retired: zero platform fees and unlimited free registration.'],
    ],

    'for-live-qa-sessions' => [
        ['title' => 'For Webinars', 'path' => '/for-webinars', 'blurb' => 'Registration, ticketing, and a join link on any platform.'],
        ['title' => 'For Virtual Conferences', 'path' => '/for-virtual-conferences', 'blurb' => 'One event per day with a timed agenda inside it and one join link.'],
        ['title' => 'Polls', 'path' => '/features/polls', 'blurb' => 'Add a poll to any event: a question and two to ten choices.'],
        ['title' => 'Online Events', 'path' => '/features/online-events', 'blurb' => 'Paste a Zoom, Meet or stream link once and the ticket and listing follow.'],
    ],

    'for-webinars' => [
        ['title' => 'For Virtual Conferences', 'path' => '/for-virtual-conferences', 'blurb' => 'One event per day with a timed agenda inside it and one join link.'],
        ['title' => 'For Live Q&A Sessions', 'path' => '/for-live-qa-sessions', 'blurb' => 'Registration, ticketing and email for a live Q&A on any platform.'],
        ['title' => 'Online Events', 'path' => '/features/online-events', 'blurb' => 'Paste a Zoom, Meet or stream link once and the ticket and listing follow.'],
        ['title' => 'Eventzilla Alternative', 'path' => '/eventzilla-alternative', 'blurb' => 'A flat-rate alternative to Eventzilla with zero per-ticket fees.'],
    ],

    'accelevents-alternative' => [
        ['title' => 'Whova Alternative', 'path' => '/whova-alternative', 'blurb' => 'Transparent pricing, with no custom quotes or sales calls.'],
        ['title' => 'Splash Alternative', 'path' => '/splash-alternative', 'blurb' => 'Zero platform fees and open source, without enterprise pricing.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'addevent-alternative' => [
        ['title' => 'Google Calendar Alternative', 'path' => '/google-calendar-alternative', 'blurb' => 'When a Google Calendar link is not enough.'],
        ['title' => 'Luma Alternative', 'path' => '/luma-alternative', 'blurb' => 'Custom domains, zero platform fees, and open source flexibility.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Tockify Alternative', 'path' => '/tockify-alternative', 'blurb' => 'An embeddable calendar that also takes registrations.'],
    ],

    'brown-paper-tickets-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'TicketLeap Alternative', 'path' => '/ticketleap-alternative', 'blurb' => 'No per-ticket fees, payouts into your own account, and calendar sync.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'dice-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    // A reader who has finished comparing wants the logistics, so the migration page leads here
    // and replaces the Luma row rather than being appended: related-pages.blade.php sizes the grid
    // as lg:grid-cols-{min(count, 4)}, so a fifth entry would leave one card alone on the last row.
    // /luma-alternative stays reachable from /compare and from its sibling comparison pages.
    'eventbrite-alternative' => [
        ['title' => 'Move from Eventbrite', 'path' => '/switch-from-eventbrite', 'blurb' => 'What comes across in the import, what does not, and what to do on the first day.'],
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'eventzilla-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Humanitix Alternative', 'path' => '/humanitix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus selfhosting.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'google-calendar-alternative' => [
        ['title' => 'AddEvent Alternative', 'path' => '/addevent-alternative', 'blurb' => 'Ticketing and public event pages, not just calendar buttons.'],
        ['title' => 'Meetup Alternative', 'path' => '/meetup-alternative', 'blurb' => 'Zero platform fees and custom domains, without a subscription.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Timely Alternative', 'path' => '/timely-alternative', 'blurb' => 'A free plan and ticketing with zero platform fees, not an annual add-on.'],
    ],

    'humanitix-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Tito Alternative', 'path' => '/tito-alternative', 'blurb' => 'Flat pricing instead of a percentage of every ticket.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Zeffy Alternative', 'path' => '/zeffy-alternative', 'blurb' => 'Open source ticketing for any organizer, with zero platform fees.'],
    ],

    'luma-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Meetup Alternative', 'path' => '/meetup-alternative', 'blurb' => 'Zero platform fees and custom domains, without a subscription.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Partiful Alternative', 'path' => '/partiful-alternative', 'blurb' => 'Free RSVPs, plus recurring events and a public schedule page.'],
    ],

    'meetup-alternative' => [
        ['title' => 'Luma Alternative', 'path' => '/luma-alternative', 'blurb' => 'Custom domains, zero platform fees, and open source flexibility.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'For Meetup Groups', 'path' => '/for-meetup-groups', 'blurb' => 'Free RSVPs with a cap, a page the group owns, and no organizer fee to keep it.'],
        ['title' => 'Mobilizon Alternative', 'path' => '/mobilizon-alternative', 'blurb' => 'Open source events with ticketing and calendar sync built in.'],
    ],

    'pretix-alternative' => [
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Tito Alternative', 'path' => '/tito-alternative', 'blurb' => 'Flat pricing instead of a percentage of every ticket.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'sched-alternative' => [
        ['title' => 'Whova Alternative', 'path' => '/whova-alternative', 'blurb' => 'Transparent pricing, with no custom quotes or sales calls.'],
        ['title' => 'Accelevents Alternative', 'path' => '/accelevents-alternative', 'blurb' => 'Zero platform fees, instant setup, and open source flexibility.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'splash-alternative' => [
        ['title' => 'Accelevents Alternative', 'path' => '/accelevents-alternative', 'blurb' => 'Zero platform fees, instant setup, and open source flexibility.'],
        ['title' => 'Whova Alternative', 'path' => '/whova-alternative', 'blurb' => 'Transparent pricing, with no custom quotes or sales calls.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'ticket-tailor-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Tito Alternative', 'path' => '/tito-alternative', 'blurb' => 'Flat pricing instead of a percentage of every ticket.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'tito-alternative' => [
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Pretix Alternative', 'path' => '/pretix-alternative', 'blurb' => 'Flat pricing instead of per-ticket fees, plus AI features.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'whova-alternative' => [
        ['title' => 'Sched Alternative', 'path' => '/sched-alternative', 'blurb' => 'Zero platform fees, calendar sync, and open source flexibility.'],
        ['title' => 'Accelevents Alternative', 'path' => '/accelevents-alternative', 'blurb' => 'Zero platform fees, instant setup, and open source flexibility.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'timely-alternative' => [
        ['title' => 'The Events Calendar Alternative', 'path' => '/the-events-calendar-alternative', 'blurb' => 'Event pages, ticketing and calendar sync without a WordPress plugin.'],
        ['title' => 'Tockify Alternative', 'path' => '/tockify-alternative', 'blurb' => 'An embeddable calendar that also takes registrations.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'the-events-calendar-alternative' => [
        ['title' => 'Timely Alternative', 'path' => '/timely-alternative', 'blurb' => 'A free plan and ticketing with zero platform fees, not an annual add-on.'],
        ['title' => 'Tockify Alternative', 'path' => '/tockify-alternative', 'blurb' => 'An embeddable calendar that also takes registrations.'],
        ['title' => 'WordPress Event Calendar', 'path' => '/wordpress-event-calendar', 'blurb' => 'Put your live calendar on a WordPress page with one Custom HTML block.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'tockify-alternative' => [
        ['title' => 'Timely Alternative', 'path' => '/timely-alternative', 'blurb' => 'A free plan and ticketing with zero platform fees, not an annual add-on.'],
        ['title' => 'The Events Calendar Alternative', 'path' => '/the-events-calendar-alternative', 'blurb' => 'Event pages, ticketing and calendar sync without a WordPress plugin.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'bandsintown-alternative' => [
        ['title' => 'Facebook Events Alternative', 'path' => '/facebook-events-alternative', 'blurb' => 'An events page you own, with ticketing and calendar sync.'],
        ['title' => 'Songkick Alternative', 'path' => '/songkick-alternative', 'blurb' => 'Tour dates on your own page, with registrations and tickets built in.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'posh-alternative' => [
        ['title' => 'Partiful Alternative', 'path' => '/partiful-alternative', 'blurb' => 'Free RSVPs, plus recurring events and a public schedule page.'],
        ['title' => 'Bandsintown Alternative', 'path' => '/bandsintown-alternative', 'blurb' => 'Your own tour-date page with ticketing and calendar sync.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'partiful-alternative' => [
        ['title' => 'Posh Alternative', 'path' => '/posh-alternative', 'blurb' => 'Ticketing for parties and nights out with zero platform fees.'],
        ['title' => 'Facebook Events Alternative', 'path' => '/facebook-events-alternative', 'blurb' => 'An events page you own, with ticketing and calendar sync.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'facebook-events-alternative' => [
        ['title' => 'Partiful Alternative', 'path' => '/partiful-alternative', 'blurb' => 'Free RSVPs, plus recurring events and a public schedule page.'],
        ['title' => 'AllEvents Alternative', 'path' => '/allevents-alternative', 'blurb' => 'Your own schedule page, with no booking fee on the tickets you sell.'],
        ['title' => 'Community Event Calendar', 'path' => '/community-event-calendar', 'blurb' => 'One shared calendar for a town, fed by the organizers in it.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'zeffy-alternative' => [
        ['title' => 'Facebook Events Alternative', 'path' => '/facebook-events-alternative', 'blurb' => 'An events page you own, with ticketing and calendar sync.'],
        ['title' => 'Hi.Events Alternative', 'path' => '/hi-events-alternative', 'blurb' => 'Two open source ticketing platforms, compared line by line.'],
        ['title' => 'For Nonprofits', 'path' => '/for-nonprofits', 'blurb' => 'Galas, volunteer days and campaigns, with zero platform fees on every ticket.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'hi-events-alternative' => [
        ['title' => 'Mobilizon Alternative', 'path' => '/mobilizon-alternative', 'blurb' => 'Open source events with ticketing and calendar sync built in.'],
        ['title' => 'Zeffy Alternative', 'path' => '/zeffy-alternative', 'blurb' => 'Open source ticketing for any organizer, with zero platform fees.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'mobilizon-alternative' => [
        ['title' => 'Hi.Events Alternative', 'path' => '/hi-events-alternative', 'blurb' => 'Two open source ticketing platforms, compared line by line.'],
        ['title' => 'Facebook Events Alternative', 'path' => '/facebook-events-alternative', 'blurb' => 'An events page you own, with ticketing and calendar sync.'],
        ['title' => 'For Nonprofits', 'path' => '/for-nonprofits', 'blurb' => 'Galas, volunteer days and campaigns, with zero platform fees on every ticket.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    // TicketLeap's standout is its free seating chart builder, so the seating page is the card a
    // reader weighing the two is most likely to want next.
    'ticketleap-alternative' => [
        ['title' => 'Ticket Fee Calculator', 'path' => '/ticket-fee-calculator', 'blurb' => 'What TicketLeap, Eventbrite and six other platforms take from your ticket sales.'],
        ['title' => 'Allocated Seating', 'path' => '/features/allocated-seating', 'blurb' => 'Draw your room once and let buyers pick their own seats from a map of it.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    // Songkick lists artists' dates for their fans, so the reader is a performer or the venue they
    // play: the cards follow them rather than the other ticketing comparisons.
    'songkick-alternative' => [
        ['title' => 'Bandsintown Alternative', 'path' => '/bandsintown-alternative', 'blurb' => 'Your own tour-date page with ticketing and calendar sync.'],
        ['title' => 'For Musicians', 'path' => '/for-musicians', 'blurb' => 'Tour dates, gigs, and fans on one link.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run your live music calendar in one place.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
    ],

    // AllEvents is a city-wide discovery feed, so the cards lead to what an organizer gets instead:
    // the fee maths, a page of their own for every event, and a shared calendar a town can run itself.
    'allevents-alternative' => [
        ['title' => 'Ticket Fee Calculator', 'path' => '/ticket-fee-calculator', 'blurb' => 'What AllEvents, Eventbrite and six other platforms take from your ticket sales.'],
        ['title' => 'Event Landing Page', 'path' => '/event-landing-page', 'blurb' => 'Every event gets a free page: the flyer, the date, a map and a ticket button.'],
        ['title' => 'Community Event Calendar', 'path' => '/community-event-calendar', 'blurb' => 'One shared calendar for a town, fed by the organizers in it.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'replace' => [
        ['title' => 'Google Forms Replacement', 'path' => '/google-forms-replacement', 'blurb' => 'Built-in ticketing, payments, and public event pages.'],
        ['title' => 'Mailchimp Replacement', 'path' => '/mailchimp-replacement', 'blurb' => 'Built-in newsletters with A/B testing and attendee management.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'calendly-replacement' => [
        ['title' => 'Doodle Replacement', 'path' => '/doodle-replacement', 'blurb' => 'Beyond date polls: public pages, ticketing, and a calendar.'],
        ['title' => 'Appointments', 'path' => '/features/appointments', 'blurb' => 'Write your hours down once and let guests pick an open time.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'canva-replacement' => [
        ['title' => 'Event Graphics', 'path' => '/features/event-graphics', 'blurb' => 'Auto-generate flyers and share graphics from your events.'],
        ['title' => 'Linktree Replacement', 'path' => '/linktree-replacement', 'blurb' => 'One bio link that shows your actual dates, not just buttons.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'doodle-replacement' => [
        ['title' => 'Calendly Replacement', 'path' => '/calendly-replacement', 'blurb' => 'One-on-one bookings plus public events with ticketing.'],
        ['title' => 'SurveyMonkey Replacement', 'path' => '/surveymonkey-replacement', 'blurb' => 'Purpose-built event signup with ticketing and payments.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'google-forms-replacement' => [
        ['title' => 'SurveyMonkey Replacement', 'path' => '/surveymonkey-replacement', 'blurb' => 'Purpose-built event signup with ticketing and payments.'],
        ['title' => 'Google Sheets Replacement', 'path' => '/google-sheets-replacement', 'blurb' => 'Attendee management and ticket sales tracking in one place.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'google-sheets-replacement' => [
        ['title' => 'Notion Replacement', 'path' => '/notion-replacement', 'blurb' => 'Public event pages, ticketing, and Google Calendar sync.'],
        ['title' => 'Trello Replacement', 'path' => '/trello-replacement', 'blurb' => 'Public event pages, built-in ticketing, and payments.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'linktree-replacement' => [
        ['title' => 'Squarespace Replacement', 'path' => '/squarespace-replacement', 'blurb' => 'Purpose-built event pages and ticketing, with no site to build.'],
        ['title' => 'Canva Replacement', 'path' => '/canva-replacement', 'blurb' => 'Graphics and flyers generated from your event details.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'mailchimp-replacement' => [
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Send branded newsletters to your followers and ticket buyers.'],
        ['title' => 'SurveyMonkey Replacement', 'path' => '/surveymonkey-replacement', 'blurb' => 'Purpose-built event signup with ticketing and payments.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'notion-replacement' => [
        ['title' => 'Trello Replacement', 'path' => '/trello-replacement', 'blurb' => 'Public event pages, built-in ticketing, and payments.'],
        ['title' => 'Google Sheets Replacement', 'path' => '/google-sheets-replacement', 'blurb' => 'Attendee management and ticket sales tracking in one place.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'qr-code-generator-replacement' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Squarespace Replacement', 'path' => '/squarespace-replacement', 'blurb' => 'Purpose-built event pages and ticketing, with no site to build.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'squarespace-replacement' => [
        ['title' => 'Linktree Replacement', 'path' => '/linktree-replacement', 'blurb' => 'One bio link that shows your actual dates, not just buttons.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Event Landing Page', 'path' => '/event-landing-page', 'blurb' => 'Every event gets a free page: the flyer, the date, a map and a ticket button.'],
    ],

    'surveymonkey-replacement' => [
        ['title' => 'Google Forms Replacement', 'path' => '/google-forms-replacement', 'blurb' => 'Built-in ticketing, payments, and public event pages.'],
        ['title' => 'Doodle Replacement', 'path' => '/doodle-replacement', 'blurb' => 'Beyond date polls: public pages, ticketing, and a calendar.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'trello-replacement' => [
        ['title' => 'Notion Replacement', 'path' => '/notion-replacement', 'blurb' => 'Public event pages, ticketing, and Google Calendar sync.'],
        ['title' => 'Google Sheets Replacement', 'path' => '/google-sheets-replacement', 'blurb' => 'Attendee management and ticket sales tracking in one place.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    // Phase 6.3 generic pages. /features/registration is the free, no-payment half of ticketing
    // and links across to it for the paid half; the two calendar pages point at the feature pages
    // they explain for one audience or one website builder.
    'features/registration' => [
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Ticket Waitlist', 'path' => '/features/waitlist', 'blurb' => 'A sold-out date offers a returned seat to one person at a time.'],
        ['title' => 'Check-in Dashboard', 'path' => '/features/check-in', 'blurb' => 'Watch the room fill up while you are standing at the door.'],
        ['title' => 'For Meetup Groups', 'path' => '/for-meetup-groups', 'blurb' => 'Free RSVPs with a cap, a page the group owns, and no organizer fee to keep it.'],
    ],

    'features/booking-requests' => [
        ['title' => 'Custom Fields', 'path' => '/features/custom-fields', 'blurb' => 'Add your own questions to the ticket, registration and event request forms.'],
        ['title' => 'Appointments', 'path' => '/features/appointments', 'blurb' => 'Write your hours down once and let guests pick an open time.'],
        ['title' => 'Community Event Calendar', 'path' => '/community-event-calendar', 'blurb' => 'One shared calendar for a town, fed by the organizers in it.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run every show from one schedule.'],
    ],

    'community-event-calendar' => [
        ['title' => 'For Curators', 'path' => '/for-curators', 'blurb' => 'Build a local events guide that fills itself from venue and talent schedules.'],
        ['title' => 'For Community Centers', 'path' => '/for-community-centers', 'blurb' => 'Programs, classes, hall-hire requests and events in one place.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Sort what arrives into the sections of your guide.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => "Send the week's highlights to your subscribers."],
    ],

    // Cross-linked with /squarespace-replacement, which owns the comparison with a website
    // builder while this page owns the anatomy of the page itself.
    'event-landing-page' => [
        ['title' => 'Squarespace Replacement', 'path' => '/squarespace-replacement', 'blurb' => 'Purpose-built event pages and ticketing, with no site to build.'],
        ['title' => 'Selling Tickets', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in and a live door dashboard, with zero platform fees.'],
        ['title' => 'Free Registration & RSVP', 'path' => '/features/registration', 'blurb' => 'Free sign-ups with a cap per date, a waitlist and a QR code, on every plan.'],
        ['title' => 'Custom Domain', 'path' => '/features/custom-domain', 'blurb' => 'Use your own domain instead of the default subdomain.'],
    ],

    'wordpress-event-calendar' => [
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'Embed Tickets', 'path' => '/features/embed-tickets', 'blurb' => 'Put the ticket checkout on your own website with one iframe tag.'],
        ['title' => 'The Events Calendar Alternative', 'path' => '/the-events-calendar-alternative', 'blurb' => 'Event pages, ticketing and calendar sync without a WordPress plugin.'],
        ['title' => 'Free Registration & RSVP', 'path' => '/features/registration', 'blurb' => 'Free sign-ups with a cap per date, a waitlist and a QR code, on every plan.'],
    ],
];
