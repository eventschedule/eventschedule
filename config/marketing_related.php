<?php

/**
 * Related marketing pages map.
 *
 * Keyed by request path (e.g. 'features/ticketing', 'for-musicians', 'pricing').
 * Each value is an array of 3-6 related entries with: title, path, blurb.
 *
 * Used by <x-marketing.related-pages /> to render a "Related" strip above the
 * footer. Adding a new key here automatically enables the strip on that page.
 */

return [
    'features/ticketing' => [
        ['title' => 'AI Features', 'path' => '/features/ai', 'blurb' => 'Parse events from text, generate flyers, and translate content with AI.'],
        ['title' => 'Calendar Sync', 'path' => '/features/calendar-sync', 'blurb' => 'Two-way sync with Google Calendar and any CalDAV server.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run every show from one schedule with QR check-in built in.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Sell tickets without 3.7% + $1.79 per-ticket platform fees.'],
        ['title' => 'Pricing', 'path' => '/pricing', 'blurb' => 'See what is included on Free, Pro, and Enterprise plans.'],
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
];
