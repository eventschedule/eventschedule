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
    'features/ticketing' => [
        ['title' => 'Allocated Seating', 'path' => '/features/allocated-seating', 'blurb' => 'Draw your room once and let buyers pick their own seats from a map of it.'],
        ['title' => 'AI Features', 'path' => '/features/ai', 'blurb' => 'Parse events from text, generate flyers, and translate content with AI.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run every show from one schedule with QR check-in built in.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Sell tickets without 3.7% + $1.79 per-ticket platform fees.'],
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

    'compare' => [
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'What each plan costs, with no platform fees on any of them.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Everything the comparison grid is measuring, explained.'],
        ['title' => 'Replace Your Tools', 'path' => '/replace', 'blurb' => 'Swapping a spreadsheet or a form rather than a platform.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run it on your own server with every paid feature included.'],
    ],

    'selfhost' => [
        ['title' => 'White-Label SaaS', 'path' => '/saas', 'blurb' => 'Turn your install into a ticketing business you own.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature included in the selfhosted build.'],
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
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets and sync sold-out status to your calendar.'],
        ['title' => 'Google Calendar Alternative', 'path' => '/google-calendar-alternative', 'blurb' => 'When a Google Calendar link is not enough.'],
    ],

    'for-musicians' => [
        ['title' => 'For DJs', 'path' => '/for-djs', 'blurb' => 'DJ sets, residencies, and guest spots in one place.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Fill the calendar at the venues you play.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to your shows with zero platform fees.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
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
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
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
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Why venues are moving off Eventbrite.'],
    ],

    // The for-theaters page already links the neighbouring audience pages
    // inline, so this strip carries the features a run reaches for.
    'for-theaters' => [
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a run once, with dark days and a closing performance.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Named ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep mainstage, studio and family programming apart.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the people who follow your theater, with open rates.'],
    ],

    // The for-music-venues page already links the neighbouring audience pages
    // inline, so this strip carries the features a show day reaches for.
    'for-music-venues' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => "Keep each room's listings apart on one link."],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a residency once, and skip the weeks you are dark.'],
        ['title' => 'Newsletters', 'path' => '/features/newsletters', 'blurb' => 'Email the people who follow your venue, with open rates.'],
    ],

    // The for-nightclubs page already links the neighbouring audience pages
    // inline, so this strip carries the features the door side reaches for.
    'for-nightclubs' => [
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Ticket types, QR check-in, and zero platform fees.'],
        ['title' => 'Recurring Events', 'path' => '/features/recurring-events', 'blurb' => 'Set a residency once, and skip the weeks you close.'],
        ['title' => 'Sub-schedules', 'path' => '/features/sub-schedules', 'blurb' => 'Keep every night in its own lane on one link.'],
        ['title' => 'Analytics', 'path' => '/features/analytics', 'blurb' => 'See which nights the interest is actually landing on.'],
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
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
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
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
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
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
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
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'features/sub-schedules' => [
        ['title' => 'Custom Labels', 'path' => '/features/custom-labels', 'blurb' => 'Rename the words on your public schedule, across 34 labels.'],
        ['title' => 'Embed Calendar', 'path' => '/features/embed-calendar', 'blurb' => 'Embed your calendar on any website with one line of code.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
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

    'caldav' => [
        ['title' => 'Google Calendar', 'path' => '/google-calendar', 'blurb' => 'How the two-way Google Calendar integration works.'],
        ['title' => 'Outlook Calendar', 'path' => '/outlook-calendar', 'blurb' => 'Two-way sync with Outlook and Microsoft 365, plus Teams links.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'google-calendar' => [
        ['title' => 'Outlook Calendar', 'path' => '/outlook-calendar', 'blurb' => 'Two-way sync with Outlook and Microsoft 365, plus Teams links.'],
        ['title' => 'CalDAV', 'path' => '/caldav', 'blurb' => 'Sync with any CalDAV-compatible calendar server.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'outlook-calendar' => [
        ['title' => 'Google Calendar', 'path' => '/google-calendar', 'blurb' => 'How the two-way Google Calendar integration works.'],
        ['title' => 'CalDAV', 'path' => '/caldav', 'blurb' => 'Sync with any CalDAV-compatible calendar server.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
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
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-farmers-markets' => [
        ['title' => 'For Food Trucks', 'path' => '/for-food-trucks-and-vendors', 'blurb' => "One link that always has today's stop."],
        ['title' => 'For Community Centers', 'path' => '/for-community-centers', 'blurb' => 'Programs, classes, hall-hire requests and events in one place.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
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
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-online-classes' => [
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'For Webinars', 'path' => '/for-webinars', 'blurb' => 'Registration, ticketing, and a join link on any platform.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-virtual-conferences' => [
        ['title' => 'For Webinars', 'path' => '/for-webinars', 'blurb' => 'Registration, ticketing, and a join link on any platform.'],
        ['title' => 'For Live Q&amp;A Sessions', 'path' => '/for-live-qa-sessions', 'blurb' => 'Registration, ticketing and email for a live Q&amp;A on any platform.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-visual-artists' => [
        ['title' => 'For Art Galleries', 'path' => '/for-art-galleries', 'blurb' => 'A six-week hang is one recurring event, not thirty entries.'],
        ['title' => 'For Workshop Instructors', 'path' => '/for-workshop-instructors', 'blurb' => 'Announce classes, sell spots, and build multi-session series.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-watch-parties' => [
        ['title' => 'For Live Q&amp;A Sessions', 'path' => '/for-live-qa-sessions', 'blurb' => 'Registration, ticketing and email for a live Q&amp;A on any platform.'],
        ['title' => 'For Online Classes', 'path' => '/for-online-classes', 'blurb' => 'Sell online classes with registration and recurring sessions.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'for-workshop-instructors' => [
        ['title' => 'For Online Classes', 'path' => '/for-online-classes', 'blurb' => 'Sell online classes with registration and recurring sessions.'],
        ['title' => 'For Fitness & Yoga', 'path' => '/for-fitness-and-yoga', 'blurb' => 'Share your class schedule and sell drop-ins and class passes.'],
        ['title' => 'Use Cases', 'path' => '/use-cases', 'blurb' => 'Event scheduling for musicians, venues, restaurants and theaters.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
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
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'brown-paper-tickets-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'dice-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Ticket Tailor Alternative', 'path' => '/ticket-tailor-alternative', 'blurb' => 'Zero platform fees, open source flexibility, and AI features.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'eventbrite-alternative' => [
        ['title' => 'Luma Alternative', 'path' => '/luma-alternative', 'blurb' => 'Custom domains, zero platform fees, and open source flexibility.'],
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
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'humanitix-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Tito Alternative', 'path' => '/tito-alternative', 'blurb' => 'Flat pricing instead of a percentage of every ticket.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'luma-alternative' => [
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Meetup Alternative', 'path' => '/meetup-alternative', 'blurb' => 'Zero platform fees and custom domains, without a subscription.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
    ],

    'meetup-alternative' => [
        ['title' => 'Luma Alternative', 'path' => '/luma-alternative', 'blurb' => 'Custom domains, zero platform fees, and open source flexibility.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Keep more of every ticket you sell.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
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
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
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
];
