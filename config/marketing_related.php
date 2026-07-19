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

    'pricing' => [
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature on one page, with the plan each one needs.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Selfhost', 'path' => '/selfhost', 'blurb' => 'Run Event Schedule on your own server at no cost.'],
        ['title' => 'Compare Alternatives', 'path' => '/compare', 'blurb' => 'See how Event Schedule stacks up against other platforms.'],
    ],

    'selfhost' => [
        ['title' => 'White-Label SaaS', 'path' => '/saas', 'blurb' => 'Turn your install into a ticketing business you own.'],
        ['title' => 'All Features', 'path' => '/features', 'blurb' => 'Every feature included in the selfhosted build.'],
        ['title' => 'AI Features', 'path' => '/features/ai', 'blurb' => 'Connect your own Gemini key for AI import and blog generation.'],
        ['title' => 'Developer Docs', 'path' => '/docs/developer', 'blurb' => 'REST API reference and contributor guide.'],
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

    'for-venues' => [
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run every show from one schedule.'],
        ['title' => 'For Bars & Pubs', 'path' => '/for-bars', 'blurb' => 'Fill the room with trivia, bands, and events.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets with QR check-in and zero platform fees.'],
        ['title' => 'Eventbrite Alternative', 'path' => '/eventbrite-alternative', 'blurb' => 'Why venues are moving off Eventbrite.'],
    ],

    'for-bars' => [
        ['title' => 'For Restaurants', 'path' => '/for-restaurants', 'blurb' => 'Fill every seat with events and tastings.'],
        ['title' => 'For Breweries & Wineries', 'path' => '/for-breweries-and-wineries', 'blurb' => 'Run tasting rooms and release parties.'],
        ['title' => 'Ticketing', 'path' => '/features/ticketing', 'blurb' => 'Sell tickets to ticketed events with zero platform fees.'],
        ['title' => 'For Music Venues', 'path' => '/for-music-venues', 'blurb' => 'Run your live music calendar in one place.'],
    ],
];
